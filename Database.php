<?php

/**
 * Relative URL from the current script to install.php in the app root.
 */
if (!function_exists('tt_install_url')) {
  function tt_install_url()
  {
    $appRoot = realpath(__DIR__);
    $scriptFile = isset($_SERVER['SCRIPT_FILENAME'])
      ? realpath($_SERVER['SCRIPT_FILENAME'])
      : false;

    if ($appRoot === false || $scriptFile === false) {
      return 'install.php';
    }

    $scriptDir = dirname($scriptFile);
    if ($scriptDir === $appRoot) {
      return 'install.php';
    }

    $prefix = '';
    $current = $scriptDir;
    while ($current !== $appRoot && $current !== dirname($current)) {
      $prefix .= '../';
      $current = dirname($current);
    }

    if ($current !== $appRoot) {
      return 'install.php';
    }

    return $prefix . 'install.php';
  }
}

/**
 * True when the executing script is the application root index.php.
 * Schema/version checks run only there, not on every page request.
 */
if (!function_exists('tt_is_root_index')) {
  function tt_is_root_index()
  {
    $scriptFile = isset($_SERVER['SCRIPT_FILENAME'])
      ? realpath($_SERVER['SCRIPT_FILENAME'])
      : false;
    $rootIndex = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'index.php');

    return $scriptFile !== false && $rootIndex !== false && $scriptFile === $rootIndex;
  }
}

if (isset($conn) && $conn instanceof mysqli) {
  return;
}

$configPath = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

if (!file_exists($configPath)) {
  $installUrl = tt_install_url();
  if (!headers_sent()) {
    header('Location: ' . $installUrl);
    exit;
  }
  die(
    'Configuration file not found. Please <a href="' .
    htmlspecialchars($installUrl, ENT_QUOTES, 'UTF-8') .
    '">run the installer</a>.'
  );
}

require_once $configPath;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
  if (tt_is_root_index()) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'migrate.php';
    $conn = new mysqli($dbhost, $dbuser, $dbpass);
    $conn->set_charset('utf8mb4');
    tt_ensure_database_and_migrate($conn, $db);
  } else {
    $conn = new mysqli($dbhost, $dbuser, $dbpass, $db);
    $conn->set_charset('utf8mb4');
  }
} catch (mysqli_sql_exception $e) {
  error_log('Database connection error: ' . $e->getMessage());
  die(
    'Database connection failed. Please check your username/password in config.php and try again. ' .
    '<br><em>Detailed error messages are logged by the server.</em>'
  );
} catch (RuntimeException $e) {
  error_log('Database migration error: ' . $e->getMessage());
  die(
    'Database setup failed: ' .
    htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
  );
}

<?php
/**
 * Apply pending SQL migration files from /sql and track them in schema_migrations.
 */

/**
 * @return string Absolute path to the application root
 */
function tt_app_root()
{
  return dirname(__DIR__);
}

/**
 * @return string[] Sorted basenames of sql/*.sql
 */
function tt_list_migration_files()
{
  $sqlDir = tt_app_root() . DIRECTORY_SEPARATOR . 'sql';
  $files = glob($sqlDir . DIRECTORY_SEPARATOR . '*.sql');
  if ($files === false) {
    return [];
  }
  $basenames = array_map('basename', $files);
  sort($basenames, SORT_STRING);
  return $basenames;
}

/**
 * Ensure schema_migrations exists and apply any pending sql files.
 *
 * @param mysqli $conn Connection with the target database selected
 * @return string[] Filenames applied during this run
 * @throws RuntimeException on migration failure
 */
function tt_run_migrations(mysqli $conn)
{
  $conn->query(
    "CREATE TABLE IF NOT EXISTS `schema_migrations` (
      `filename` VARCHAR(255) NOT NULL,
      `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`filename`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
  );

  $files = tt_list_migration_files();
  if ($files === []) {
    return [];
  }

  $applied = [];
  $result = $conn->query('SELECT `filename` FROM `schema_migrations`');
  while ($row = $result->fetch_assoc()) {
    $applied[$row['filename']] = true;
  }

  // Existing install without a migration history: mark current files applied, do not replay.
  if ($applied === [] && tt_table_exists($conn, 'entries')) {
    $stmt = $conn->prepare(
      'INSERT INTO `schema_migrations` (`filename`) VALUES (?)'
    );
    foreach ($files as $filename) {
      $stmt->bind_param('s', $filename);
      $stmt->execute();
      $applied[$filename] = true;
    }
    $stmt->close();
    return [];
  }

  $sqlDir = tt_app_root() . DIRECTORY_SEPARATOR . 'sql';
  $newlyApplied = [];

  foreach ($files as $filename) {
    if (isset($applied[$filename])) {
      continue;
    }

    $path = $sqlDir . DIRECTORY_SEPARATOR . $filename;
    $sql = file_get_contents($path);
    if ($sql === false) {
      throw new RuntimeException('Could not read migration file: ' . $filename);
    }

    if (trim($sql) === '') {
      tt_record_migration($conn, $filename);
      $newlyApplied[] = $filename;
      continue;
    }

    if (!$conn->multi_query($sql)) {
      throw new RuntimeException(
        'Migration failed (' . $filename . '): ' . $conn->error
      );
    }

    do {
      if ($result = $conn->store_result()) {
        $result->free();
      }
    } while ($conn->more_results() && $conn->next_result());

    if ($conn->errno) {
      throw new RuntimeException(
        'Migration failed (' . $filename . '): ' . $conn->error
      );
    }

    tt_record_migration($conn, $filename);
    $newlyApplied[] = $filename;
  }

  return $newlyApplied;
}

/**
 * @param mysqli $conn
 * @param string $table
 */
function tt_table_exists(mysqli $conn, $table)
{
  $stmt = $conn->prepare(
    'SELECT 1 FROM information_schema.tables
     WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1'
  );
  $stmt->bind_param('s', $table);
  $stmt->execute();
  $stmt->store_result();
  $exists = $stmt->num_rows > 0;
  $stmt->close();
  return $exists;
}

/**
 * @param mysqli $conn
 * @param string $filename
 */
function tt_record_migration(mysqli $conn, $filename)
{
  $stmt = $conn->prepare(
    'INSERT INTO `schema_migrations` (`filename`) VALUES (?)'
  );
  $stmt->bind_param('s', $filename);
  $stmt->execute();
  $stmt->close();
}

/**
 * Create the database if missing, select it, then run migrations.
 *
 * @param mysqli $conn Server-level connection (may or may not have a DB selected)
 * @param string $dbName
 * @return string[] Filenames applied during this run
 */
function tt_ensure_database_and_migrate(mysqli $conn, $dbName)
{
  if (!preg_match('/^[A-Za-z0-9_]+$/', $dbName)) {
    throw new RuntimeException('Invalid database name.');
  }

  $conn->query('CREATE DATABASE IF NOT EXISTS `' . $dbName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
  $conn->select_db($dbName);
  $conn->set_charset('utf8mb4');

  return tt_run_migrations($conn);
}

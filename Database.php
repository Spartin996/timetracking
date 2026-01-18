<?php

require_once 'config.php';

// Create connection
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
	$conn = new mysqli($dbhost, $dbuser, $dbpass, $db);
	$conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
	
  //print_r($e);
  if (stripos($e->getMessage(), 'Unknown database') !== false) {
    // log full error for admins, but show a generic message to users
    error_log("Database connection error: " . $e->getMessage());

    //consolelog the error
    echo "<script>console.error('Database connection error: " . addslashes($e->getMessage()) . "');</script>";
    // check if the user would like to create the DB
    // Ask the user whether to create the DB. If no decision yet, show a JS confirm and exit.
    if (!isset($_GET['create_db'])) {
      echo "<script>
      var base = window.location.href.split('?')[0];
      if (confirm('Database does not exist. Create it now?')) {
        window.location = base + '?create_db=1';
      } else {
        window.location = base + '?create_db=0';
      }
      </script>";
      exit;
    }

    // If the user explicitly declined, stop execution.
    if ($_GET['create_db'] !== '1') {
      die('Database creation cancelled by user.');
    }

    $conn = new mysqli($dbhost, $dbuser, $dbpass);

    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS " . $db;
    if ($conn->query($sql) === TRUE) {
      echo "Database created successfully<br>";
      $conn->select_db($db);
    $sqlFiles = [
    'sql/V1.0_time_sheets.sql',
    'sql/V1.1_add_interrupted.sql',
    'sql/V1.2_add_tags.sql',
    'sql/V2.0_add_category_type.sql',
    'sql/V2.0_add_projects.sql',
    'sql/V2.0_add_project_to_entries.sql',
    'sql/V2.1_add_settings.sql',
    'sql/V2.2_add_auto_project_id.sql'
  ];

  foreach ($sqlFiles as $file) {
    if (file_exists($file)) {
      $sql = file_get_contents($file);
        if ($conn->multi_query($sql)) {
          echo "<script> console.log('Executed: " . $file . "');</script>";
          // Clear any pending results
          while ($conn->next_result()) {;}
        } else {
          echo "Error in " . $file . ": " . $conn->error . "<br>";
        }
      }
    }
  } else {
      echo "Error creating database: " . $conn->error . "<br>";
    }
  } else {

    error_log("Database connection error: " . $e->getMessage());

  //consolelog the error
    echo "<script>console.error('Database connection error: " . addslashes($e->getMessage()) . "');</script>";

    die("Database connection failed. Please check your username/password and try again. <br><em>Detailed error messages are logged in the console.</em>");

  }
}

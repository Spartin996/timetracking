<?php

require_once 'config.php';

// Create connection
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
	$conn = new mysqli($dbhost, $dbuser, $dbpass, $db);
	$conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
	
  // log full error for admins, but show a generic message to users
  error_log("Database connection error: " . $e->getMessage());
  
  //consolelog the error
  echo "<script>console.error('Database connection error: " . addslashes($e->getMessage()) . "');</script>";
  die("Database connection failed. Please check your username/password and try again. <br><em>Detailed error messages are logged in the console.</em>");
}


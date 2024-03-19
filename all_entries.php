<?php
//report page to provide summary to a report page

// V1.1 Created 2024-01-07 By MM - First version

//get the environment settings and functions
include "functions.php";

$start_date = issetget("start_date") . " 00:00:00";
$start_date = displayTime($start_date, "sql");

$end_date = issetget("end_date") . " 00:00:00";
$end_date = displayTime($end_date, "sql");

?>

<!DOCTYPE html>
<html>

<head>
  <title>Chocolate Log - edit entry</title>
  <link rel='stylesheet' href='styles/styles.css'>
  <script src='functions.js'></script>
</head>

<body>
  <?php include "Nav.php"; ?>

  <?php
  echo showEntries($start_date, $end_date, "all");
  ?>

</body>

</html>
<?php
//filters page to provide filters to a report page

// V1.1 Created 2024-01-07 By MM - First version

//get the environment settings and functions
include "functions.php";
?>

<!DOCTYPE html>
<html>

<head>
  <title>Chocolate Log - edit entry</title>
  <link rel='stylesheet' href='styles/styles.css'>
  <script src="functions.js"></script>
</head>

<body>
  <?php include "nav.php"; ?>
  <form method="GET" action="all_entries.php" class="filterEntry">
    <div>
      <label for='start_date'>Report starting?: </label>
      <input name='start_date' type='date'>
    </div>
    <div>
      <label for='end_date'>Report ending?: </label>
      <input name='end_date' type='date'>
    </div>

    <div>
      <label>Imagine lots of check boxes for categories: </label>
    </div>

    <input type='submit' value="Generate Report">

  </form>
</body>

</html>
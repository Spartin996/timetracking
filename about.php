<?php
//about page and utilities 

// V1.1 Created 2024-01-06 By MM - First version



//get the environment settings and functions
include "functions.php";

?>


<!DOCTYPE html>
<html>

<head>
  <title>Chocolate Log - About</title>
  <link rel='stylesheet' href='styles/styles.css'>
</head>

<body>
  <?php
  include "nav.php";
  ?>

  <p>This page will have information about the software and how to install it.</p>

  <h2>Recalculate All Entries</h2>
  <p>The time spent on a task is calculated as the entry is added to the database. It is than stored in the database as a value rounded to the nearest minute. If the value is less than a minute it will add 1 minute to the entry.</p>
  <p>If you need to recalculate this you can click this link <a href='recalc_all.php' target='_blank'>Recalculate all time spent for all entries in the database.</a></p>
</body>

</html>
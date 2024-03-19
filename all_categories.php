<?php
//view all categories page 

// V1.1 Created 2024-01-07 By MM - First version



//get the environment settings and functions
include "functions.php";
include "connect.ini";

//Get all the entries from categories and add them to a table
$table = "<table><tr><th>Display</th><th>Description</th><th>Active</th><th>Sequence</th></tr>";
$sql = "SELECT id, display_name, `description`, active, seq
  FROM categories
  ORDER BY seq asc";
$result = $conn->query($sql);
while ($row = mysqli_fetch_array($result)) {
  $table .= "<tr onclick='newWindow(`categories.php?id=" . $row['id'] . "`)' ><td>" . $row['display_name'] . "</td><td>" . $row['description'] . "</td><td>" . $row['active'] . "</td><td>" . $row['seq'] . "</td></tr>";
}
$table .= "</table>";
?>


<!DOCTYPE html>
<html>

<head>
  <title>Chocolate Log - Categories</title>
  <link rel='stylesheet' href='styles/styles.css'>
  <script src='functions.js'></script>
</head>

<body>
  <?php
  include "Nav.php";
  ?>

  <p>This shows a list of all categories in the system and allows you to edit them.</p>
  <p><a href='#' onclick="newWindow('categories.php')">Add a new Category</a></p>

  <?php echo $table; ?>

  <?php




  ?>
</body>

</html>
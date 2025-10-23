<?php
//view all categories page 

// V1.1 Created 2024-01-07 By MM - First version



//Get the environment settings and functions
include "../php/functions.php";
include "../Database.php";
session_start();


//Get all the entries from categories and add them to a table
$table = "<table><tr><th>Display</th><th>Description</th><th>Active</th><th>Entries</th><th>Projects</th><th>Sequence</th></tr>";
$sql = "SELECT id, display_name, `description`, active, entries, projects, seq
  FROM categories
  ORDER BY seq asc";
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");
while ($row = mysqli_fetch_array($result)) {
  $table .= "<tr onclick='newWindow(`categories.php?id=" . $row['id'] . "`)' >
    <td>" . $row['display_name'] . "</td>
    <td>" . $row['description'] . "</td>
    <td>" . $row['active'] . "</td>
    <td>" . $row['entries'] . "</td>
    <td>" . $row['projects'] . "</td>

    <td>" . $row['seq'] . "</td>
    </tr>";
}
$table .= "</table>";
?>


<!DOCTYPE html>
<html>

<head>
  <title><?php echo $_SESSION['settings']['name']['value']; ?> - Categories</title>
  <link rel='stylesheet' href='../styles/styles.css'>
  <script src='../js/functions.js'></script>
</head>

<body>
  <?php
  include "../partials/nav.php";
  ?>

  <p>This shows a list of all categories in the system and allows you to edit them.</p>
  <p><a href='#' onclick="newWindow('categories.php')">Add a new Category</a></p>

  <?php echo $table; ?>

  <?php




  ?>
</body>

</html>
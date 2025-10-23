<?php
//view all tags page 

// V1.1 Created 2024-10-13 By MM - First version



//Get the environment settings and functions
include "../php/functions.php";
include "../Database.php";
session_start();


//Get all the entries from tags and add them to a table
$table = "<table><tr><th>Tag</th></tr>";
$sql = "SELECT id, tag
  FROM tags
  ORDER BY tag asc";
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");
while ($row = mysqli_fetch_array($result)) {
  $table .= "<tr onclick='newWindow(`tags.php?id=" . $row['id'] . "`)' ><td>" . $row['tag'] . "</td></tr>";
}
$table .= "</table>";
?>


<!DOCTYPE html>
<html>

<head>
  <title><?php echo $_SESSION['settings']['name']['value']; ?> - Tags</title>
  <link rel='stylesheet' href='../styles/styles.css'>
  <script src='../js/functions.js'></script>
</head>

<body>
  <?php
  include "../partials/nav.php";
  ?>

  <p>This shows a list of all tags in the system and allows you to add or delete them.</p>
  <p><a href='#' onclick="newWindow('tags.php')">Add a new Tag</a></p>

  <?php echo $table; ?>

  <?php




  ?>
</body>

</html>
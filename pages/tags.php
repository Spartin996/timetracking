<?php
//Tags page to edit or create tags

// V1.1 Created 2024-10-13 By MM - First version

//Get the environment settings and functions
include "../php/functions.php";
include "../connect.ini";
session_start();

//todo add support for delete and disable

$id = issetget("id");

//Form is submitted, GET variables
if (isset($_GET["hasBeenSub"])) {
  $tag = issetget("tag");

  // if ID must be a edit
  if ($id != "") {
    //update sql
    $sql = "UPDATE `tags` 
      SET `tag` = '" . $tag . "'
      WHERE id = " . $id;
  } else {
    //No ID, insert
    $sql = "INSERT INTO `tags` 
    (`id`, `tag`)
     VALUES (NULL, '" . $tag . "');";
  }
  $run = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  echo "<script>window.close();</script>";
} else {
  //Form Not submitted

  //Creating a new entry, leave variables and form blank
  if ($id == "") {
    $tag = issetget("tag");
  } else {
    // ID found so go get the data from the database for editing
    $sql = "SELECT id, tag
    FROM tags
    WHERE id = " . $id;
    $result = $conn->query($sql);
    logAction("Ran SQL on DB, " . $sql, "file");
    $row = mysqli_fetch_array($result);
    $tag = $row['tag'];
  }
}


?>


<!DOCTYPE html>
<html>

<head>
  <title><?php echo $_SESSION['settings']['name']['value']; ?> - Edit Tag</title>
  <link rel='stylesheet' href='../styles/styles.css'>
  <script src="../js/functions.js"></script>
</head>

<body>
  <form method="GET" action="tags.php" class="manEditCats">

    <input name='hasBeenSub' type="hidden" value="submitted">

    <input name='id' type="hidden" value="<?php echo $id; ?>">
    <div>
      <label for='tag'>Tag Name: </label>
      <input name='tag' type='text' value="<?php echo $tag; ?>">
    </div>

    <input type='submit' value='Save'>

  </form>
</body>

</html>
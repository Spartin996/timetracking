<?php
//Page that stops current job

// V1.1 Created 2024-01-06 By MM - First version

include '../php/functions.php';
include '../connect.ini';

//Get time in SQL format
$time = date('Y-m-d H:i:s', time());

if (isset($_GET['comment'])) {
  $comment = $_GET['comment'];
} else {
  $comment = "NULL";
}
$interrupted = issetget("interrupted", "N");
$tags = issetget("tags");

//You were interrupted 
//todo check this logic and move it below the update, while this works it is not the best way to do it
//the only it works is because the select statement is getting the lowest id, while working this is not safe.
if (isset($_GET['interrupted'])){

  if (isset($_GET['categories'])) {
    $category = $_GET['categories'];
  } else {
    echo "ERROR! you forgot to select a category.<br> Return <a href=index.php>home</a>";
  }

$sql = "INSERT INTO entries 
  (`id`, `categories_id`, `start_time`, `end_time`, `last_modified`) 
  VALUES 
  (NULL, '" . $category . "', '" . $time . "', NULL, '" . $time . "')";
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");

}



//Get the ID for the open job
$sql = "SELECT id, start_time, end_time 
FROM entries
WHERE end_time IS NULL 
Limit 1";
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");
$row = mysqli_fetch_array($result);
$entryId = $row['id'];

$timespent = timeBetween($time, $row['start_time']);

$sql = "UPDATE entries SET end_time = '" . $time . "', minutes = '" . $timespent . "', interrupted = '" . $interrupted . "', comment = '" . $comment . "', tags = '" . $tags . "' WHERE id = " . $entryId;
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");
// go it index.php when done
header("Location: index.php");



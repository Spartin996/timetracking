<?php
//Page that start a new job

// V1.1 Created 2024-01-06 By MM - First version

include 'functions.php';

//get time in SQL format
$time = date('Y-m-d H:i:s', time());


if (isset($_GET['categories'])) {
  $category = $_GET['categories'];
} else {
  echo "ERROR! you forgot to select a category.<br> Return <a href=index.php>home</a>";
}
if (isset($_GET['comment'])) {
  $comment = $_GET['comment'];
} else {
  $comment = "NULL";
}

$sql = "INSERT INTO entries 
  (`id`, `categories_id`, `start_time`, `end_time`, `comment`, `last_modified`) 
  VALUES 
  (NULL, '" . $category . "', '" . $time . "', NULL, '" . $comment . "', '" . $time . "')";
$result = $db->query($sql)->fetchAll();

// go it index.php when done
header("Location: index.php");

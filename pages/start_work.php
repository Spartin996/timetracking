<?php
//Page that start a new job

// V1.1 Created 2024-01-06 By MM - First version

include '../php/functions.php';
include '../Database.php';

//get time in SQL format
$time = date('Y-m-d H:i:s', time());


$category = issetrequest('categories');
if ($category === null || $category === '') {
  echo "ERROR! you forgot to select a category.<br> Return <a href=index.php>home</a>";
  exit;
}
$comment = issetrequest('comment', '');
$tags = issetrequest('tags', '');
$interrupted = issetrequest('interrupted', 'N');
if ($interrupted !== 'Y') {
  $interrupted = 'N';
}

$comment = $conn->real_escape_string($comment);
$tags = $conn->real_escape_string($tags);

$sql = "INSERT INTO entries 
  (`id`, `categories_id`, `start_time`, `end_time`, `comment`, `tags`, `interrupted`, `last_modified`) 
  VALUES 
  (NULL, '" . $category . "', '" . $time . "', NULL, '" . $comment . "', '" . $tags . "', '" . $interrupted . "', '" . $time . "')";
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");

// go it index.php when done
header("Location: index.php");


<?php
//Page that start a new job via ajax



include '../php/functions.php';
include '../Database.php';

//get time in SQL format
$time = date('Y-m-d H:i:s', time());
$data = json_decode(file_get_contents('php://input'), true); 


if (isset($data['category'])) {
  $category = $data['category'];
} else {
  echo "ERROR! you forgot to select a category.<br> Return <a href=index.php>home</a>";
}
if (isset($data['comment'])) {
  $comment = $data['comment'];
} else {
  $comment = "NULL";
}


$sql = "INSERT INTO entries 
  (`id`, `categories_id`, `start_time`, `end_time`, `comment`,  `project_id`, `last_modified`) 
  VALUES 
  (NULL, '" . $category . "', '" . $time . "', NULL, '" . $comment . "', '" . $data['project_id'] . "', '" . $time . "')";
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");

echo "true";


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
  $comment = $conn->real_escape_string($data['comment']);
} else {
  $comment = "";
}

$tags = "";
if (isset($data['tags']) && $data['tags'] !== null) {
  $tags = $conn->real_escape_string($data['tags']);
}

$interrupted = "N";
if (isset($data['interrupted']) && $data['interrupted'] === 'Y') {
  $interrupted = "Y";
}

$projectSql = "NULL";
if (isset($data['project_id']) && $data['project_id'] !== '' && $data['project_id'] !== null && $data['project_id'] !== 'NULL') {
  $projectSql = "'" . $conn->real_escape_string($data['project_id']) . "'";
}

$sql = "INSERT INTO entries 
  (`id`, `categories_id`, `start_time`, `end_time`, `comment`, `tags`, `interrupted`, `project_id`, `last_modified`) 
  VALUES 
  (NULL, '" . $category . "', '" . $time . "', NULL, '" . $comment . "', '" . $tags . "', '" . $interrupted . "', " . $projectSql . ", '" . $time . "')";
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");

echo "true";




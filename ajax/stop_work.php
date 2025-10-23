<?php
//Page that stops current job via ajax


include '../php/functions.php';
include '../Database.php';

//Get time in SQL format
$time = date('Y-m-d H:i:s', time());

//get the data from JSON
$data = json_decode(file_get_contents('php://input'), true); 



//Get the ID for the open job
$sql = "SELECT id, start_time, end_time 
FROM entries
WHERE end_time IS NULL 
Limit 1";
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");
$row = mysqli_fetch_array($result);
$entryId = $row['id'];



//get the data
if (isset($data['comment'])) {
  $comment = $data['comment'];
} else {
  $comment = "NULL";
}

if (isset($data["interrupted"])) {
  $interrupted = $data["interrupted"];
} else {
  $interrupted = "N";
}

if(isset($data["project_id"]) && $data["project_id"] != "") {
  $project_id = $data["project_id"];
} else {
 $project_id = "NULL";
}

if (isset($data["tags"])){
  $tags = $data["tags"];
} else {
  $tags = "";
}

$curCategories = $data['category_display'];



//You were interrupted 
if ($interrupted == "Y"){

  if (isset($data['category'])) {
    $category = $data['category'];
  } else {
    echo "ERROR! you forgot to select a category.<br> Return <a href=index.php>home</a>";
    die();
  }

$sql = "INSERT INTO entries 
  (`id`, `categories_id`, `start_time`, `end_time`, `last_modified`) 
  VALUES 
  (NULL, '" . $category . "', '" . $time . "', NULL, '" . $time . "')";
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");

}





$timespent = timeBetween($time, $row['start_time']);

$sql = "UPDATE entries 
SET end_time = '" . $time . "',
 minutes = '" . $timespent . "',
 `categories_id` = '" . $curCategories . "',
 interrupted = '" . $interrupted . "',
 comment = '" . $comment . "',
 project_id = " . $project_id . ",
 tags = '" . $tags . "'
 WHERE id = " . $entryId;
logAction("stop_work.php about to run sql on DB, " . $sql, "file");
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");

UpdateTimeOnProject($project_id);


echo "true";



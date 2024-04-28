<?php
//Page that stops current job

// V1.1 Created 2024-01-06 By MM - First version

include 'functions.php';

//Get time in SQL format
$time = date('Y-m-d H:i:s', time());

if (isset($_GET['comment'])) {
  $comment = $_GET['comment'];
} else {
  $comment = "NULL";
}


//Get the ID for the open job
$sql = "SELECT id, start_time, end_time 
FROM entries
WHERE end_time IS NULL 
Limit 1";
$result = $db->query($sql)->fetch();
$entryId = $result['id'];

$timespent = timeBetween($time, $result['start_time']);


$sql = "UPDATE entries SET end_time = '" . $time . "', minutes = '" . $timespent . "', comment = '" . $comment . "' WHERE id = " . $entryId;
$result = $db->query($sql)->fetch();
// go it index.php when done
header("Location: index.php");

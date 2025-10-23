<?php
//file to just check if a file entry is open
//this will be called by a ajax call on all pages

include "../php/functions.php";
include "../Database.php";
session_start();



$sql = "SELECT entries.id, categories_id, display_name, start_time, end_time, comment, tags, project_id 
FROM entries
LEFT JOIN categories
ON entries.categories_id = categories.id 
WHERE end_time IS NULL Limit 1;";
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");
$row = mysqli_fetch_array($result);

if ($row) {
  echo "TRUE";
} else {
  echo "FALSE";
}
<?php
//Page that stops current job

// V1.1 Created 2024-01-06 By MM - First version

include '../php/functions.php';
include '../Database.php';

//Get time in SQL format
$time = date('Y-m-d H:i:s', time());

$comment = issetrequest('comment', 'NULL');
$interrupted = postedCheckboxY('interrupted');
$follow_up = postedCheckboxY('follow_up');
$tags = issetrequest('tags', '');
$category = issetrequest('categories');
$projectId = resolvePostedProjectId($comment, $category);

//Get the ID for the open job before inserting a follow-on interrupted job
$sql = "SELECT id, start_time, end_time 
FROM entries
WHERE end_time IS NULL 
Limit 1";
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");
$row = mysqli_fetch_array($result);
$entryId = $row['id'];

$timespent = timeBetween($time, $row['start_time']);

//You were interrupted: open a new job after capturing the one being stopped
if ($interrupted === 'Y') {

  if ($category === null || $category === '') {
    echo "ERROR! you forgot to select a category.<br> Return <a href=index.php>home</a>";
    exit;
  }

$sql = "INSERT INTO entries 
  (`id`, `categories_id`, `start_time`, `end_time`, `last_modified`) 
  VALUES 
  (NULL, '" . $category . "', '" . $time . "', NULL, '" . $time . "')";
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");

}

$comment = $conn->real_escape_string((string) $comment);
$tags = $conn->real_escape_string((string) $tags);
$projectSql = ($projectId === null) ? 'NULL' : "'" . (int) $projectId . "'";

$sql = "UPDATE entries SET end_time = '" . $time . "', minutes = '" . $timespent . "', interrupted = '" . $interrupted . "', follow_up = '" . $follow_up . "', comment = '" . $comment . "', tags = '" . $tags . "', project_id = " . $projectSql . " WHERE id = " . $entryId;
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");

if ($projectId !== null) {
  UpdateTimeOnProject($projectId);
}

header('Location: ' . safeReturnLocation());
exit;

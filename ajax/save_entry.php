<?php
//file to save a entry without ending it.

//include the functions and the Database.php
include '../php/functions.php';
include '../Database.php';


//get the data from JSON
$data = json_decode(file_get_contents('php://input'), true); 



$id = $data["id"];
$categories = $data["category_display"];
$start_time = date("Y-m-d H:i:s", $data["start_time"]);
$comment = $data["comment"];
//default to N
if ($data["interrupted"] == "") {
    $interrupted = "N";
} else {
    $interrupted = "Y";
}
$interrupted = $data["interrupted"];

$tags = $data["tags"];

//default to NULL
if ($data["project_id"] == "") {
  $project_id = NULL;
} else {
  $project_id = $data["project_id"];
}


$sql = "UPDATE `entries` 
      SET `categories_id` = '" . $categories . "'
      , `start_time` = '" . $start_time . "'
      , `interrupted` = '" . $interrupted . "'
      , `comment` = '" . $comment . "'
      , `tags` = '" . $tags . "'";
      //allow for a project to be added or removed
      if($project_id != NULL) {
        $sql .= ", `project_id` = '" . $project_id . "' ";
      } else {
        $sql .= ", `project_id` = NULL ";
      }
      $sql .= "WHERE id = " . $id;

//run the sql
logAction("SQL to run on DB, " . $sql, "file");
$run = $conn->query($sql);

echo "true";

?>


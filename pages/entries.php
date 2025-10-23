<?php
//edit a entry

// V1.1 Created 2024-01-06 By MM - First version

//Get the environment settings and functions
include "../php/functions.php";
include "../Database.php";
session_start();

$id = issetget("id");

//Form is submitted, GET Variables
if (isset($_GET["hasBeenSub"])) {
  $categories = issetget("categories");
  $start_time = displayTime(issetget("start_time"), "sql");
  $end_time = displayTime(issetget("end_time"), "sql");
  $comment = issetget("comment");
  $interrupted = issetget("interrupted", "N");
  $minutes = timeBetween($end_time, $start_time);
  $tags = issetget("tags");
  $project_id = issetget("project_id", NULL);
  
   // if ID must be a edit
  if ($id != "") {
    //update sql
    $sql = "UPDATE `entries` 
      SET `categories_id` = '" . $categories . "'
      , `start_time` = '" . $start_time . "'
      ";
      if($end_time != NULL) {
       $sql .= ", `end_time` = '" . $end_time . "'";
      }
      $sql .= ", `minutes` = '" . $minutes . "'
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
  } else {
    //No ID, insert
    $sql = "INSERT INTO `entries`
      (`id`, `categories_id`, `start_time`, `end_time`, `minutes`, `interrupted`, `comment`, `tags`)
      VALUES (NULL, '" . $categories . "'
      , '" . $start_time . "'
      , '" . $end_time . "'
      , '" . $minutes . "'
      , '" . $interrupted . "'
      , '" . $comment . "'
      , '" . $tags . "'";
      //allow for a project to be added or removed
      if($project_id != NULL) {
        $sql .= ", '" . $project_id . "'";
      } else {
        $sql .= ", NULL";
      }
      $sql .= ")";
  }
  logAction("SQL to run on DB, " . $sql, "file");
  $run = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");

  if ($project_id != "") {
    UpdateTimeOnProject($project_id);
  }

  echo "<script>window.close();</script>";
} else {
  //Form not submitted
  //Creating a new entry, so leave blank
  if ($id == "") {
    $categories = issetget("categories");
    $start_time = date('Y-m-d H:i:s', time());
    $end_time = date('Y-m-d H:i:s', time());
    $minutes = issetget("minutes");
    $interrupted = issetget("interrupted", "N");
    $comment = issetget("comment");
    $tags = issetget("tags");
    $project_id = issetget("project_id");
  } else {
    $sql = "SELECT `id`, `categories_id`, `start_time`, `end_time`, `minutes`, `interrupted`, `comment`, `tags`, `project_id` 
    FROM entries
    WHERE id = " . $id;
    $result = $conn->query($sql);
    logAction("Ran SQL on DB, " . $sql, "file");
    $row = mysqli_fetch_array($result);
    $categories = $row["categories_id"];
    $start_time = $row["start_time"];
    $end_time = $row["end_time"];
    $minutes = $row["minutes"];
    $interrupted = $row["interrupted"];
    $comment = $row["comment"];
    $tags = $row["tags"];
    $project_id = $row["project_id"];
  }
}

$start_time = displayTime($start_time, "html");
if($end_time) {
  $end_time = displayTime($end_time, "html");
}

?>


<!DOCTYPE html>
<html>

<head>
  <title><?php echo $_SESSION['settings']['name']['value']; ?> - Edit Entry</title>
  <link rel='stylesheet' href='../styles/styles.css'>
  <script src="../js/functions.js"></script>
</head>

<body>
  <form method="GET" action="entries.php" class="manEditEntry">

    <input name='hasBeenSub' type="hidden" value="submitted">

    <input name='id' type="hidden" value=<?php echo $id; ?>>
    
    <div>
      <label for='categories'>Select a Category for the Job: </label>
      <?php echo CategoryDropList("entries", "Y", $categories); ?>
    </div>
    
    <div>
      <label for='start_time'>What time did you start?: </label>
      <input name='start_time' type='datetime-local' value='<?php echo $start_time; ?>'>
    </div>
    
    <div>
      <label for='end_time'>What time did you finish?: </label>
      <input name='end_time' type='datetime-local' value='<?php echo $end_time; ?>'>
    </div>
    
    <div>
      <span>Number of minutes spent on job: </span>
      <span id='minutes'><?php echo minutesToHours($minutes); ?></span>
    </div>
    
    <div>
      <span>Were you interrupted: </span>
      <span id='interrupted'><input name='interrupted' type='checkbox' value="Y" <?php if($interrupted == "Y") { echo "checked";} ?>></span>
    </div>
    
    <div>
      <label>Leave a comment?: </label>
      <textarea name='comment' rows='4' cols='50'><?php echo $comment; ?></textarea>
    </div>

    <div>
      <label>Project: </label>
      <?php echo generateProjectsList("project_id", $project_id); ?>
    </div>

    <div class='tags'>
      <div>
        <label>Add Tags: </label>
        <input type='text' name='addTags' id='addTags' onkeyup='showTags(this.value)'>
      </div>
      <div id='divTags'></div>
      <input type='hidden' name='tags' id='tags' value='<?php echo $tags ?>'>
      <div id='displayTags'>

      </div>
    </div>

    <input type='submit' value='Save'>

  </form>
</body>

<script>  
displayTags();

let possibleTags = [];

getPossibleTags();




</script>
</html>
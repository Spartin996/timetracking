<?php
//Home Page

// V1.1 Created 2024-01-06 By MM - First version

//get the environment settings and functions
include "functions.php";


$id = issetget("id");

//Form is submitted, GET Variables
if (isset($_GET["hasBeenSub"])) {
  $categories = issetget("categories");
  $start_time = displayTime(issetget("start_time"), "sql");
  $end_time = displayTime(issetget("end_time"), "sql");
  $comment = issetget("comment");

  $minutes = timeBetween($end_time, $start_time);
  // if ID must be a edit
  if ($id != "") {
    //update sql
    $sql = "UPDATE `entries` 
      SET `categories_id` = '" . $categories . "'
      , `start_time` = '" . $start_time . "'
      , `end_time` = '" . $end_time . "'
      , `minutes` = '" . $minutes . "'
      , `comment` = '" . $comment . "'
      WHERE id = " . $id;
  } else {
    //No ID, insert
    $sql = "INSERT INTO `entries`
      (`id`, `categories_id`, `start_time`, `end_time`, `minutes`, `comment`)
      VALUES (NULL, '" . $categories . "'
      , '" . $start_time . "'
      , '" . $end_time . "'
      , '" . $minutes . "'
      , '" . $comment . "')";
  }
  $run = $db->query($sql)->fetch();
  echo "<script>window.close();</script>";
} else {
  //Form not submitted
  //Creating a new entry, so leave blank
  if ($id == "") {
    $categories = issetget("categories");
    $start_time = date('Y-m-d H:i:s', time());
    $end_time = date('Y-m-d H:i:s', time());
    $minutes = issetget("minutes");
    $comment = issetget("comment");
  } else {
    $sql = "SELECT `id`, `categories_id`, `start_time`, `end_time`, `minutes`, `comment` 
    FROM entries
    WHERE id = " . $id;
    $result = $db->query($sql)->fetch();
    $categories = $result["categories_id"];
    $start_time = $result["start_time"];
    $end_time = $result["end_time"];
    $minutes = $result["minutes"];
    $comment = $result["comment"];
  }
}

$start_time = displayTime($start_time, "html");
$end_time = displayTime($end_time, "html");

?>


<!DOCTYPE html>
<html>

<head>
  <title>Chocolate Log - edit entry</title>
  <link rel='stylesheet' href='styles/styles.css'>
  <script src="functions.js"></script>
</head>

<body>
  <form method="GET" action="entries.php" class="manEditEntry">

    <input name='hasBeenSub' type="hidden" value="submitted">

    <input name='id' type="hidden" value=<?php echo $id; ?>>
    <div>
      <label for='categories'>Select a Category for the Job: </label>
      <?php echo generateJobsDrop("Y", $categories); ?>
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
      <label>Leave a comment?: </label>
      <textarea name='comment' rows='4' cols='50'><?php echo $comment; ?></textarea>
    </div>

    <input type='submit' value='Save'>

  </form>
</body>

</html>
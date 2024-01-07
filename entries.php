<?php
//Home Page

// V1.1 Created 2024-01-06 By MM - First version

//get the environment settings and functions
include "functions.php";


$id = issetget("id");

//check if form is submitted, if so update row
if(isset($_GET['hasBeenSub'])) {
  $categories_id = issetget("categories_id");
  $start_time = displayTime(issetget("start_time"), "sql");
  $end_time = displayTime(issetget("end_time"), "sql");
  $comment = issetget("comment");

  $minutes = timeBetween($end_time, $start_time);
  echo $minutes;

  if($id != "") {
    //update sql goes here
    $sql = "UPDATE `entries` 
      SET `categories_id` = " . $categories_id . "
      , `start_time` = " . $start_time . "
      , `end_time` = " . $end_time . "
      , `minutes` = " . $minutes . "
      , `comment` = " . $comment . "
      WHERE id = " . $id;
  } else {
    //insert goes here

  }


} else {
//I have not submitted the form yet
//check if I am creating a new entry, if so leave blank
  if($id == "") {
  $categories_id = issetget("categories_id");
  $start_time = issetget("start_time");
  $end_time = issetget("end_time");
  $minutes = issetget("minutes");
  $comment = issetget("comment");
  } else {
  $sql = "SELECT `id`, `categories_id`, `start_time`, `end_time`, `minutes`, `comment` 
    FROM entries
    WHERE id = " . $id;
  $result = $conn->query($sql);
  $row = mysqli_fetch_array($result);
  $categories_id = $row['categories_id'];
  $start_time = $row['start_time'];
  $end_time = $row['end_time'];
  $minutes = $row['minutes'];
  $comment = $row['comment'];
  }
}

$start_time = displayTime($start_time, "html");
$end_time = displayTime($end_time, "html");

?>


<!DOCTYPE html>
<html>
  <head>
    <title>Chocolate Log - edit entry</title>
    <link rel=stylesheet href=styles/styles.css>
  </head>
  <body>
    <form method="GET" action="entries.php" class="manEditEntry">

      <input name=hasBeenSub type="hidden" value="submitted">

      <input name=id type="hidden" value=<?php echo $id; ?>>
      <div>
        <label for=categories>THIS WILL RESET EACH SUBMIT</label>
        <?php echo generateJobsDrop("Y", $categories_id); ?>
      </div>
      <div>
        <label for=start_time>What time did you start?</label>
        <input name=start_time type=datetime-local value=<?php echo $start_time; ?>>
      </div>
      <div>
      <label for=end_time>What time did you finish?</label>
      <input name=end_time type=datetime-local value=<?php echo $end_time; ?>>
      </div>
      <div>
        <span>Number of minutes spent on jop: </span>
        <span id=minutes><?php echo minutesToHours($minutes); ?></span>
      </div>
      <div>
        <label>Leave a comment?</label>
        <textarea name=comment rows=4 cols=50><?php echo $comment; ?></textarea>
      </div>

      <input type=submit value=Save>
      
    </form>
  </body>
</html>
<?php
//Functions for the rest of the util

// V1.1 Created 2024-01-06 By MM - First version



//get the environment settings and functions
include "connect.ini";
//this will generate the main div for index.php
//it will show either the current job with a stop button
//or a list of jobs and a start button
function startStopForm() {


  global $conn;
  //see if user is currently working
  $sql = "SELECT entries.id, categories_id, display_name, start_time, end_time, comment 
  FROM entries
  LEFT JOIN categories
  ON entries.categories_id = categories.id 
  WHERE end_time IS NULL Limit 1;";
  $result = $conn->query($sql);
  $row = mysqli_fetch_array($result);
  //print_r($row);
  if($row != ""){
    //if you are currently working on XX this will happen
    return "<span>Your current open Job is " . $row['display_name'] . ".</span><br><span>The Job has been open for <span id='timer'></span></span><br><form method=GET action=stop_work.php><br><textarea name=comment rows=4 cols=50>" . $row['comment'] . "</textarea><br><input type=submit value='Stop Work'></form>" ;
  } else {
    //if you are not currently working on anything this will happen
    return "<span>No current open job</span><br><span>Open a Job?</span> <br>Current time:<span id='timer'></span><br> <form method=GET action=start_work.php>" . generateJobsDrop('N') . "<br><textarea name=comment rows=4 cols=50></textarea><br><input type=submit value='Get To Work'></form>" ;
  }


}



//get a droplist of jobs
//$incInactive will either be Y this will show all jobs
// or N will only show active jobs
// default will hold the category id that you want selected
function generateJobsDrop($incInactive, $default = NULL) {
  //check if I should inc inactive jobs
  if($incInactive != "Y"){
    $where = "WHERE active LIKE 'Y'";
  } else {
    $where = "";
  }

  global $conn;
  //see if user is currently working
  $sql = "SELECT id, display_name FROM categories " . $where . " ORDER BY seq ASC";
	$result = $conn->query($sql);
  $output = "<select name='categories'>";
	while($row = mysqli_fetch_array($result)){
    //check if we need to set a default value on the dropbox
    if($default != NULL and $row[0] == $default) {
      $selected = " selected=selected";
    } else {
      $selected = "";
    }

	  $output .= "<option Value='" . $row[0] . "' " . $selected . ">" . $row[1] . "</option>";
	}
  $output .= "</select>";
  return $output;

}


//function to show all entries for a period for a category in a table
function showEntriesTable($dateStart, $dateEnd, $categories) {
  global $conn;
  //if categories is all get all ID from the categories table
  if($categories == "all"){
    $categories = "";
    $sql = "SELECT id FROM categories";
    $result = $conn->query($sql);
    while($row = mysqli_fetch_array($result)){

      $categories .= $row[0] . ",";
    }
    $categories = rtrim($categories, ",");
  }

  //add 00:00:00 to start date and midnight to end date so it is inclusive
  $dateStart .= " 00:00:00";
  $dateEnd .= " 23:59:59";
 

  $sql = "SELECT entries.id, categories_id, display_name, start_time, end_time, `minutes`, comment 
  FROM entries
  LEFT JOIN categories
  ON entries.categories_id = categories.id 
  WHERE start_time > '" . $dateStart . "'
  AND start_time < '" . $dateEnd . "'
  AND categories_id IN (" . $categories . ")";
  $result = $conn->query($sql);
    $table = "<table id=showEntries><tr><th>Job</th><th>Start Time</th><th>End Time</th><th>Time Taken</th><th>Comments</th></tr>";
  while($row = mysqli_fetch_array($result)) {
    $table .= "<tr onclick='newWindow(`entries.php?id=" . $row['id'] . "`)' ><td>" . $row['display_name'] . "</td><td>" . displayTime($row['start_time'], "12") . "</td><td>" . displayTime($row['end_time'], "12") . "</td><td>" . minutesToHours($row['minutes']) . "</td><td>" . $row['comment'] ." </td></tr>"; 
  }

  $table .= "</table>";
  

  // add a sumary at the top

  //return it
  return $table;

}


//function to get yesterdays date
function getOldDate($goBack) {
  $goBack = "-" . $goBack . " days";
  return date_create()->modify($goBack)->format('Y-m-d');
}

//recalc all minutes fields in all entries
function recalcAllMinutes() {
  global $conn;
  $sql = "SELECT id, start_time, end_time FROM entries";
  $result = $conn->query($sql);
  while($row = mysqli_fetch_array($result)) {
    $entryId = $row['id'];
    //calculate the mintues on a job
  $timespent = timeBetween($row['end_time'], $row['start_time']);

    $sql = "UPDATE entries SET `minutes` = '" . $timespent . "' WHERE id = " . $entryId;  
    $update = $conn->query($sql);
  }

  Return "ALL Entries Recalculated";
}

//take minutes and convert to hours and minutes
function minutesToHours($minutes) {
  $hours = floor($minutes / 60);
  $min = $minutes - ($hours * 60);
  if($min < 10) {
    $min = "0" . $min;
  }
  return $hours . ":" . $min;
}

//Generate a summary table
function showEntriesSummary($dateStart, $dateEnd, $categories) {
  global $conn;
  if($categories == "all"){
    $categories = "";
    $sql = "SELECT id FROM categories ORDER BY seq ASC";
    $result = $conn->query($sql);
    while($row = mysqli_fetch_array($result)){

      $categories .= $row[0] . ",";
    }
    $categories = rtrim($categories, ",");
    $categories = explode(',', $categories);
  }

  //add 00:00:00 to start date and midnight to end date so it is inclusive
  $dateStart .= " 00:00:00";
  $dateEnd .= " 23:59:59";

  $table = "<table id=summary><tr><th>Job</th><th>Time Spent</th></tr>";
  foreach ($categories as $category) {
    
    $sql = "SELECT id, display_name FROM categories WHERE id = " . $category;
    $result = $conn->query($sql);
    while($row = mysqli_fetch_array($result)){
      $displayName = $row['display_name'];
    }
    
    $sql = "SELECT SUM(`minutes`) 
    FROM entries 
    WHERE categories_id = " . $category . "
    AND start_time > '" . $dateStart . "'
    AND start_time < '" . $dateEnd . "'";
    $result = $conn->query($sql);
    while($row = mysqli_fetch_array($result)){
      $minutes = $row['0'];
    }
    if ($minutes != "") {
    $table .= "<tr><td>" . $displayName . "</td><td>" . minutesToHours($minutes) . "</td></tr>";
    }
  }
  //add a summary to the bottom
  
  $displayName = "<strong>ALL JOBS</strong>";
    
    $sql = "SELECT SUM(`minutes`) 
    FROM entries 
    WHERE start_time > '" . $dateStart . "'
    AND start_time < '" . $dateEnd . "'";
    $result = $conn->query($sql);
    while($row = mysqli_fetch_array($result)){
      $minutes = $row['0'];
    }
    if ($minutes != "") {
    $table .= "<tr><td>" . $displayName . "</td><td>" . minutesToHours($minutes) . "</td></tr>";
    }
  $table .= "</table>";
  return $table;
}

//show a summary table and a full table
//categories = all will get all categories and generate the reports
function showEntries($dateStart, $dateEnd, $categories) {
  $return = showEntriesSummary($dateStart, $dateEnd, $categories);
  $return .= showEntriesTable($dateStart, $dateEnd, $categories);
  return $return; 

}

//if a open job exists get the unix time for it
function openJob() {
  global $conn;
  //see if user is currently working
  $sql = "SELECT entries.id, categories_id, display_name, start_time, end_time, comment 
  FROM entries
  LEFT JOIN categories
  ON entries.categories_id = categories.id 
  WHERE end_time IS NULL Limit 1;";
  $result = $conn->query($sql);
  $row = mysqli_fetch_array($result);
  if($row == ""){
    return "No Open Job";
  } else {
    return strtotime($row['start_time']);
  }
}

//function to set a variable to value if it does not exsist
function issetget($var, $default = null) {
  if(isset($_GET[$var])){
    return $_GET[$var];
  } else {
    return NULL;
  }
}

//validate a date from the db as either am,pm or for a input
//supported formats
//12 - 12 hour
//24 - 24 hour
//sql - sql format
//html - html format for the value of a datetime fields
function displayTime($time, $format) {
  //if entries ongoing show it
  if($time == NULL) {
    return "ONGOING";
  }
  $unix = strtotime($time);
  if($format == "12") {
    return date('Y-m-d g:i a', $unix);
  }
  if($format == "24") {
    return date("Y-m-d H:i", $unix);
  }
  if($format == "sql") {
    return date("Y-m-d H:i:s", $unix);
  }
  if($format == "html") {
    $return = date("Y-m-d", $unix) . "T" . date("H:i", $unix);
    return $return;
  }
}

function timeBetween($end_time, $start_time) {
  $timespent = strtotime($end_time) - strtotime($start_time);
  //divide by 60 to get minutes and floor it to make it a whole number
  $timespent = floor($timespent / 60);
  //if it s less than 0 make it 1 so that you always do at least a minutes work
  if($timespent <= 0) {
    $timespent = 1;
  }
  return $timespent;
}
?>
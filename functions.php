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
    return "<span>You should currently be working on " . $row['display_name'] . ".</span><br><span>Would you like to stop?</span><br><form method=GET action=stop_work.php><br><textarea name=comment rows=4 cols=50></textarea><br><input type=submit value='Stop Work'></form>" ;
  } else {
    //if you are not currently working on anything this will happen
    return "<span>Not at work you lazy bugger!</span><br><span>What should you be working on?</span> <br> <form method=GET action=start_work.php>" . generateJobsDrop('N') . "<br><textarea name=comment rows=4 cols=50></textarea><br><input type=submit value='Get To Work'></form>" ;
  }


}



//get a droplist of jobs
//$incInactive will either be Y this will show all jobs
// or N will only show active jobs
function generateJobsDrop($incInactive) {
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

	  $output .= "<option Value='" . $row[0] . "'>" . $row[1] . "</option>";
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
  AND end_time < '" . $dateEnd . "'
  AND categories_id IN (" . $categories . ")";
  $result = $conn->query($sql);
    $table = "<table id=showEntries><tr><th>Job</th><th>Start Time</th><th>End Time</th><th>Time Taken</th><th>Comments</th></tr>";
  while($row = mysqli_fetch_array($result)) {
    $table .= "<tr><td>" . $row['display_name'] . "</td><td>" . $row['start_time'] . "</td><td>" . $row['end_time'] . "</td><td>" . minutesToHours($row['minutes']) . "</td><td>" . $row['comment'] ." </td></tr>"; 
  }

  $table .= "</table>";
  

  // add a sumary at the top

  //return it
  return $table;

}


//function to get yesterdays date
function getYesterday() {
  return date_create()->modify('-1 day')->format('Y-m-d');
}

//recalc all minutes fields in all entries
function recalcAllMinutes() {
  global $conn;
  $sql = "SELECT id, start_time, end_time FROM entries";
  $result = $conn->query($sql);
  while($row = mysqli_fetch_array($result)) {
    $entryId = $row['id'];
    //calculate the mintues on a job
    //convert to a unix time stamp and calculate the nubmer of seconds between start and finish
  $timespent = strtotime($row['end_time']) - strtotime($row['start_time']);
  //divide by 60 to get minutes
    $timespent = $timespent / 60;

    $sql = "UPDATE entries SET `minutes` = '" . $timespent . "' WHERE id = " . $entryId;  
    $update = $conn->query($sql);
  }

  Return "ALL Entries Recalculated";
}

//take minutes and convert to hours and minutes
function minutesToHours($minutes) {
  $hours = floor($minutes / 60);
  $min = $minutes - ($hours * 60);
  return $hours . ":" . $min;
}

//Generate a summary table
function showEntriesSummary($dateStart, $dateEnd, $categories) {

}

//show a summary table and a full table
function showEntries($dateStart, $dateEnd, $categories) {
  $return = showEntriesSummary($dateStart, $dateEnd, $categories);
  $return .= showEntriesTable($dateStart, $dateEnd, $categories);
  return $return; 

}

?>
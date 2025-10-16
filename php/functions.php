<?php
//Functions for the rest of the util

// V1.1 Created 2024-01-06 By MM - First version


//get the environment settings and functions
include "../connect.ini";


//dump and Die
function dd($variable) {
  echo "<pre>";
  var_dump($variable);
  echo "</pre>";
  die();
}


//Generate the main div for index.php
//todo move this to a new file
function startStopForm()
{
//it will show either the current job with a stop button
//or a list of jobs and a start button

  global $conn;
  //see if user is currently working
  $sql = "SELECT entries.id, categories_id, display_name, start_time, end_time, comment, tags 
  FROM entries
  LEFT JOIN categories
  ON entries.categories_id = categories.id 
  WHERE end_time IS NULL Limit 1;";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  $row = mysqli_fetch_array($result);
  if ($row) {
    //if you are currently working on XX this will happen
    return "<span>Your current open Job is " . $row['display_name'] . ".</span><br><span>The Job has been open for <span id='timer'></span></span><br><form method=GET action=stop_work.php><br><textarea name=comment rows=4 cols=50>" . $row['comment'] . "</textarea><br>
    " . CategoryDropList('entries','N') . " <br>    <div>
      <span>Were you interrupted: </span> <span id='interrupted'><input name='interrupted' type='checkbox' value='Y'></span>
    </div>    <div class='tags'>
      <div>
        <label>Add Tags: </label>
        <input type='text' name='addTags' id='addTags' onkeyup='showTags(this.value)'>
      </div>
      <div id='divTags'></div>
      <input type='hidden' name='tags' id='tags' value='" . $row['tags'] . "'>
      <div id='displayTags'>

      </div>
    </div>
<input type=submit value='Stop Work'></form>";
  } else {
    //if you are not currently working on anything this will happen
    return "<span>No current open job</span><br><span>Open a Job?</span> <br>Current time:<span id='timer'></span><br> <form method=GET action=start_work.php>" . CategoryDropList('entries','N') . "<br><textarea name=comment rows=4 cols=50></textarea><br><input type=submit value='Get To Work'></form>";
  }
}



//get a droplist of jobs
function CategoryDropList($type, $incInactive, $default = NULL)
{
  //$incInactive will either be Y this will show all jobs
  //or N will only show active jobs
  //default will hold the category id that you want selected
  
  //check if I should inc inactive jobs
  if ($incInactive != "Y") {
    $where = "WHERE active LIKE 'Y' ";
  } else {
    $where = "";
  }

  if ($where != "") {
    $where .= "AND ";
  } else{
    $where .= "WHERE ";
  }

if ($type == "entries" || $type == "curEntries") {
    $where .= "entries LIKE 'Y'";
  } else if ($type == "projects") {
    $where .= "projects LIKE 'Y'";
  }


  global $conn;
  //see if user is currently working
  $sql = "SELECT id, display_name FROM categories " . $where . " ORDER BY seq ASC";
  logAction("CategoryDropList about to run SQL on DB, " . $sql, "file");
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  if ($type == "projects") {
    $output = "<select name='projects' id='project_cat'>";
      //add a unknown option so I do not need to include it in the SQL
    $output .= "<option value='0'>Unknown</option>";
  } else if ($type == "curEntries") {
    $output = "<select name='curEntryCat' id='curEntryCat'>";
  } else {
    $output = "<select name='categories' id='categories'>";
  }



  while ($row = mysqli_fetch_array($result)) {
    //check if we need to set a default value on the dropbox
    if ($default != NULL and $row['id'] == $default) {
      $selected = " selected=selected";
    } else {
      $selected = "";
    }

    $output .= "<option Value='" . $row['id'] . "' " . $selected . ">" . $row['display_name'] . "</option>";
  }
  $output .= "</select>";
  return $output;
}




//function to show all entries for a period for a category in a table
function showEntriesTable($dateStart, $dateEnd, $categories, $order = "asc")
{
  global $conn;
  //if categories is all get all ID from the categories table
  if ($categories == "all") {
    $categories = "";
    $sql = "SELECT id FROM categories";
    $result = $conn->query($sql);
    logAction("Ran SQL on DB, " . $sql, "file");
    while ($row = mysqli_fetch_array($result)) {

      $categories .= $row['id'] . ",";
    }
  }
    $categories = rtrim($categories, ",");




  $sql = "SELECT entries.id, categories_id, display_name, start_time, end_time, entries.minutes, `interrupted`, `comment`, `tags`, `project_id`, projects.title 
  FROM entries
  LEFT JOIN categories
  ON entries.categories_id = categories.id
  LEFT JOIN projects
  ON entries.project_id = projects.id
  
    WHERE start_time > '" . $dateStart . "'
    AND start_time < '" . $dateEnd . "'
    AND categories_id IN (" . $categories . ")
    ORDER BY start_time " . $order;
  
    $result = $conn->query($sql);
  logAction("Ran SQL on DB from ShowEntriesTable, " . $sql, "file");
  $table = "<table id=showEntries><tr onclick=tableToCSV(this)><th>Category</th><th>Start Time</th><th>End Time</th><th>Time Taken</th><th>Interrupted</th><th>Comments</th><th>Tags</th><th>Project</th></tr>";
  while ($row = mysqli_fetch_array($result)) {
    $table .= "<tr onclick='newWindow(`entries.php?id=" . $row['id'] . "`)' >
    <td>" . $row['display_name'] . "</td>
    <td>" . displayTime($row['start_time'], setting('date_view')) . "</td>
    <td>" . displayTime($row['end_time'], setting('date_view')) . "</td>
    <td>" . minutesToHours($row['minutes']) . "</td>
    <td>" . $row['interrupted'] . "</td>
    <td>" . $row['comment'] . "</td>
    <td>" . dislpayTags($row['tags']) . "</td>
    <td>" . $row['title'] . "</td>
    </tr>";
  }
  //todo add tags to table with a cap at xxx length, skipped the cap for now.

  $table .= "</table>";


  // add a summary at the top

  //return it
  return $table;
}


//function to get yesterdays date
function getOldDate($goBack)
{
  $goBack = "-" . $goBack . " days";
  return date_create()->modify($goBack)->format('Y-m-d');
}

//recalc all minutes fields in all entries
function recalcAllMinutes()
{
  global $conn;
  $sql = "SELECT id, start_time, end_time FROM entries";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  while ($row = mysqli_fetch_array($result)) {
    $entryId = $row['id'];
    //calculate the mintues on a job
    $timespent = timeBetween($row['end_time'], $row['start_time']);

    $sql = "UPDATE entries SET `minutes` = '" . $timespent . "' WHERE id = " . $entryId;
    $update = $conn->query($sql);
    logAction("Ran SQL on DB, " . $sql, "file");
  }

  return "ALL Entries Recalculated";
}

//take minutes and convert to hours and minutes
function minutesToHours($minutes)
{
  //if minutes is empty return 0:00
  if ($minutes == "") {
    return "0:00";
  }

  $hours = floor($minutes / 60);
  $min = $minutes - ($hours * 60);
  $min = floor($min);
  if ($min < 10) {
    $min = "0" . $min;
  }
    return $hours . ":" . $min;
}


//get all categories
function getAllCategoriesCSV() {
  global $conn;
  $categories = "";
  $sql = "SELECT id FROM categories ORDER BY seq ASC";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  while ($row = mysqli_fetch_array($result)) {

    $categories .= $row['id'] . ",";
  }
  $categories = rtrim($categories, ",");

  return $categories;
}


//Generate a summary table
function showEntriesSummary($dateStart, $dateEnd, $categories)
{
  global $conn;
  if ($categories == "all") {
    $categories = getAllCategoriesCSV();
  }
    $categories = rtrim($categories, ",");
    $categories = explode(',', $categories);


  $table = "<table id=summary><tr onclick=tableToCSV(this)><th>Category</th><th>Time Spent</th></tr>";
  foreach ($categories as $category) {

    $sql = "SELECT id, display_name FROM categories WHERE id = " . $category;
    $result = $conn->query($sql);
    logAction("Ran SQL on DB, " . $sql, "file");
    while ($row = mysqli_fetch_array($result)) {
      $displayName = $row['display_name'];
    }

    $sql = "SELECT SUM(`minutes`) as Sum
    FROM entries 
    WHERE categories_id = " . $category . "
    AND start_time >= '" . $dateStart . "'
    AND start_time <= '" . $dateEnd . "'";
    $result = $conn->query($sql);
    logAction("Ran SQL on DB, " . $sql, "file");
    while ($row = mysqli_fetch_array($result)) {
      $minutes = $row['Sum'];
    }
    if ($minutes != "") {
      $table .= "<tr><td>" . $displayName . "</td><td>" . minutesToHours($minutes) . "</td></tr>";
    }
  }



  //add a summary to the bottom
  $displayName = "<strong>ALL JOBS</strong>";

  $sql = "SELECT SUM(`minutes`) as Sum
    FROM entries 
    WHERE start_time > '" . $dateStart . "'
    AND start_time < '" . $dateEnd . "'";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  while ($row = mysqli_fetch_array($result)) {
    $minutes = $row['Sum'];
  }
  if ($minutes != "") {
    $table .= "<tr><td>" . $displayName . "</td><td>" . minutesToHours($minutes) . "</td></tr>";
  }

  //add a untracked row if it is a single day
  if(substr($dateStart, 0, 10) == substr($dateEnd, 0, 10)){
    $totalTime = getTimeWorked($dateStart);
    $untracked = $totalTime - $minutes;

    $table .= "<tr><td>Untracked</td><td>" . minutesToHours($untracked) . "</td></tr>";
  }

  //add interrupted count
  $displayName = "<strong>Interrupted</strong>";

  $sql = "SELECT count(`interrupted`) as Count
    FROM entries 
    WHERE start_time > '" . $dateStart . "'
    AND start_time < '" . $dateEnd . "'
    AND interrupted LIKE 'Y'";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  $row = mysqli_fetch_array($result);
    $table .= "<tr><td>" . $displayName . "</td><td>" . $row['Count'] . "</td></tr>";

  $table .= "</table>";
  return $table;
}

//show a summary table and a full table
//categories = all will get all categories and generate the reports
function showEntries($dateStart, $dateEnd, $categories, $order = "asc")
{
  $return = showEntriesSummary($dateStart, $dateEnd, $categories);
  $return .= showEntriesTable($dateStart, $dateEnd, $categories, $order);
  return $return;
}

//if a open job exists get the unix time for it
function openJob()
{
  global $conn;
  //see if user is currently working
  $sql = "SELECT entries.id, categories_id, display_name, start_time, end_time, comment 
  FROM entries
  LEFT JOIN categories
  ON entries.categories_id = categories.id 
  WHERE end_time IS NULL Limit 1;";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  $row = mysqli_fetch_array($result);
  if ($row == "") {
    return "No Open Job";
  } else {
    return strtotime($row['start_time']);
  }
}

//function to set a variable to value if it does not exist in a get
function issetget($var, $default = null)
{
  if (isset($_GET[$var]) && $_GET[$var] != "" && $_GET[$var] != NULL) {
    return $_GET[$var];
  } else {
    return $default;
  }
}

//function to set a variable to value if it does not exist in a post
function issetpost($var, $default = null)
{
  if (isset($_POST[$var]) && $_POST[$var] != "" && $_POST[$var] != NULL) {
    return $_POST[$var];
  } else {
    return $default;
  }
}

//validate a date from the db as either am,pm or for a input
//supported formats
//12 - 12 hour
//24 - 24 hour
//sql - sql format
//html - html format for the value of a datetime fields
//day - day date and 12 hour
//day_pretty - same as day but will not show day if today
function displayTime($time, $format)
{
  //if entries ongoing show it
  if ($time == NULL && $format == "sql") {
    return NULL;
  }
  if ($time == NULL) {
    return "<span id='ongoing'>ONGOING</span>";
  }
  $unix = strtotime($time);
  if ($format == "12") {
    return date('Y-m-d g:i a', $unix);
  }
  if ($format == "24") {
    return date("Y-m-d H:i", $unix);
  }
  if ($format == "sql") {
    return date("Y-m-d H:i:s", $unix);
  }
  if ($format == "html") {
    $return = date("Y-m-d", $unix) . "T" . date("H:i", $unix);
    return $return;
  }
  if ($format == "day") {
    return date('D jS g:i a', $unix);
  }
  if ($format == "day_pretty") {
    //check if it is today and if so do not show the day
    //check if it is this week and if so do not show the date
    $today = date('Y-m-d');
    $date = date('Y-m-d', $unix);
    $mostRecentSunday = date('Y-m-d', strtotime('last Sunday'));
    if ($today == $date) {
      return date('g:i a', $unix);
    } elseif ($date > $mostRecentSunday) {
      return date('D g:i a', $unix);
    } else {
      return date('D jS g:i a', $unix);
    }
  }
}

function timeBetween($end_time, $start_time)
{
  $timespent = strtotime($end_time) - strtotime($start_time);
  //divide by 60 to get minutes and floor it to make it a whole number
  $timespent = floor($timespent / 60);
  //if it s less than 0 make it 1 so that you always do at least a minutes work
  if ($timespent <= 0) {
    $timespent = 1;
  }
  return $timespent;
}

//function to log the action that is taken including any errors.
function logAction($var, $mode = 'both')
{
  if ($mode != 'file'){
    $var = htmlspecialchars($var, ENT_QUOTES);
    echo "<script>console.log('" . $var . "'); </script>";
  }
  if ($mode != 'console'){
    $logfile = fopen('../log/logfile.log', 'a');
    $date = date('F j, Y, G:i:s');
    fwrite($logfile, '****' . $date);
    fwrite($logfile, PHP_EOL . $var . PHP_EOL . PHP_EOL);
    fclose($logfile);
  }
}

//function to display a tag
function dislpayTags($tags) {
  $tags = str_replace("|", "", $tags);
  $tags = rtrim($tags, ",");

  return $tags;
}


//Get total time worked for a single day.
//This is calculated as earliest start_time - last end_time
function getTimeWorked($date){
  global $conn;
  $sql = "SELECT MIN(start_time), MAX(end_time)  FROM `entries` WHERE `start_time` LIKE '%" . substr($date,0, 10) . "%'";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  while ($row = mysqli_fetch_array($result)) {
    $minTime = $row['MIN(start_time)'];
    $maxTime = $row['MAX(end_time)'];
  }

  if (!$minTime){
    $minTime = date('Y-m-d H:i:s', time());
  }
  if (!$maxTime){
    $maxTime = date('Y-m-d H:i:s', time());
  }
  
  $totalTime = (strtotime($maxTime)-strtotime($minTime)) / 60;

  return $totalTime;
  
}


function calcAverage($val1, $val2) {
  if ($val1 == 0 || $val2 == 0) {
    return "0";
    logAction("Unable to calc average of " . $val1 . " and " . $val2);
  }
  
  
  return $val1/$val2;
}


//function to get a datalist of projects
function generateProjectsList($name, $default = NULL, $incClosed = "N") {
  //incClosed will either be Y, or N to include closed projects
  //default will hold the project id that you want selected to start with
  //name will be the name of the datalist and the ID of the input field

  global $conn;
  
  //sql to get all projects
  if ($incClosed == "Y") {
    $sql = "SELECT id, title, date_created FROM projects ORDER BY date_created ASC";
  } else {
    $sql = "SELECT id, title, date_created FROM projects WHERE date_closed IS NULL ORDER BY date_created ASC";
  }

  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");

 //make the select dropdown
 $output = "<select name='" . $name . "' id='" . $name . "'>";
 $output .= "<option value=''>Select a Project</option>";

  //add a create new project option
  $output .= "<option value='new'>Create New Project</option>";
  
 while ($row = mysqli_fetch_array($result)) {
   //check if we need to set a default value on the dropdown
  if ($default != NULL and $row['id'] == $default) {
    $selected = " selected=selected";
  } else {
    $selected = "";
  }
   $output .= "<option value='" . $row['id'] . "' " . $selected . ">" . $row['title'] . "</option>";
 }
 
 $output .= "</select>";

  //return it
  return $output;
}

//function to update the time spent on a project, $id is the project id
function UpdateTimeOnProject($id) {
  global $conn;

  if ($id == "") {
    return "0";
  }
  
  //get the sum of all entries for a project
  $sql = "SELECT SUM(`minutes`) as Sum
  FROM entries 
  WHERE project_id = " . $id;
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  while ($row = mysqli_fetch_array($result)) {
    $minutes = $row['Sum'];
  }

  if ($minutes == null) {
    $minutes = 0;
  }
  //update the project with the new time
  $sql = "UPDATE projects SET `minutes` = '" . $minutes . "' WHERE id = " . $id;
  $update = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");

  return "$minutes";
}


//function to get a all the check boxes in a list
function countCheckboxes($htmlContent) {
  $checkedCount = 0;
  $uncheckedCount = 0;
  $total = 0;

  // Load the HTML content into a DOMDocument
  $dom = new DOMDocument();
  @$dom->loadHTML($htmlContent);

  // Get all li elements
  $items = $dom->getElementsByTagName('li');

  // Loop through each li element and count based on data-list attribute
  foreach ($items as $item) {
    $dataList = $item->getAttribute('data-list');
    if ($dataList === 'checked') {
      $checkedCount++;
      $total++;
    } else if ($dataList === 'unchecked') {
      $uncheckedCount++;
      $total++;
    }
  }

  return [
    'checked' => $checkedCount,
    'unchecked' => $uncheckedCount,
    'total' => $total
  ];
}


function convertCheckboxToDB($value)  {
  if ($value == "checked") {
    return "Y";
  } elseif ($value == "unchecked") {
    return "N";
  } else {
    logAction("Error in convertDBToCheckbox, value is " . $value);
    return "ERROR";
  }
}

function convertDBToCheckbox($value)  {
  if ($value == "Y") {
    return "checked=checked";
  } elseif ($value == "N") {
    return "";
  } else {
    logAction("Error in convertDBToCheckbox, value is " . $value);
    return "ERROR";
  }
}



function getProjectEntries($id) {
  global $conn;

  if ($id == "") {
    $entries["count"] = "";
    $entries["table"] = "";
    $entries["time"] = ""; 
    return $entries;
  }

  
  //get the number of entries for a project
  $sql = "SELECT COUNT(`id`) as Count
  FROM entries 
  WHERE project_id = " . $id;
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  while ($row = mysqli_fetch_array($result)) {
    $entries = ["count" => $row['Count']];
  }

  //get the total time for a project
  //This will update the DB entry for the project and return the time
  $entries["time"] = UpdateTimeOnProject($id);

  //get each entry and add it to a table
  $table = "<table><tr onclick=tableToCSV(this)>
  <th>Category</th>
  <th>Start Time</th>
  <th>End Time</th>
  <th>Time Taken</th>
  <th>Interrupted</th>
  <th>Comments</th>
  <th>Tags</th>
  </tr>";

  $sql = "SELECT entries.id, categories_id, display_name, start_time, end_time, entries.minutes, `interrupted`, `comment`, `tags`
  FROM entries
  LEFT JOIN categories
  ON entries.categories_id = categories.id
  WHERE project_id = " . $id;
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  while ($row = mysqli_fetch_array($result)) {
    $table .= "<tr onclick='newWindow(`entries.php?id=" . $row['id'] . "`)' >
    <td>" . $row['display_name'] . "</td>
    <td>" . displayTime($row['start_time'], setting('date_view')) . "</td>
    <td>" . displayTime($row['end_time'], setting('date_view')) . "</td>
    <td>" . minutesToHours($row['minutes']) . "</td>
    <td>" . $row['interrupted'] . "</td>
    <td>" . $row['comment'] . "</td>
    <td>" . dislpayTags($row['tags']) . "</td>
    </tr>";
  }
  $table .= "</table>";

  $entries["table"] = $table;

  return $entries;
}


function setting($setting) {
  return $_SESSION['settings'][$setting]['value'];
}

//function to get the themes and add them to the HTML header
function showTheme() {
  //themes will be stored in the Session so I can just display them here

  $prim_bg = $_SESSION['settings']['primary_background']['value'];
  $second_bg = $_SESSION['settings']['secondary_background']['value'];
  $active_bg = $_SESSION['settings']['active_background']['value'];
  $neutral_white = $_SESSION['settings']['neutral_white']['value'];
  $neutral_gray = $_SESSION['settings']['neutral_gray']['value'];
  $neutral_active = $_SESSION['settings']['neutral_active']['value'];
  $header_img = $_SESSION['settings']['header_img']['value'];

  echo "<style>
  :root {
    --primary-background: " . $prim_bg . ";
    --secondary-background: " . $second_bg . ";
    --active-background: " . $active_bg . ";
    --neutral-white: " . $neutral_white . ";
    --neutral-gray: " . $neutral_gray . ";
    --neutral-active: " . $neutral_active . ";
}

  #header {
    background-image: url(" . $header_img . ");
  }
  </style>";
}


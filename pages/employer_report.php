<?php

//Generate a employer report.

include "../php/functions.php";
session_start();

//get the date range

$date_sql = date('Y-m-d', time());
$start_date = issetget("start_date", $date_sql);
$end_date = issetget("end_date", $date_sql);
$start_dateTime = displayTime($start_date, "sql");
$end_dateTime = $end_date . " 23:59:59";
$end_dateTime = displayTime($end_date, "sql");

//Set a period object to loop through
$interval = new DateInterval('P1D');
$period = new DatePeriod(new DateTime($start_date), $interval, new DateTime($end_date));

//This will have each day with summary information in it.
$totalsReport = [];

foreach ($period as $day) {
  $date = $day->format('Y-m-d');
  $dateDay = $day->format('D');

  //count entries
  $sql = "SELECT COUNT(start_time) FROM `entries` WHERE start_time LIKE '%" . $date . "%';";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  while ($row = mysqli_fetch_array($result)) {
    $numberEntries = $row['COUNT(start_time)'];
  }

  //did you worked
  if ($numberEntries > 0){
    $worked = "Y";
  } else {
    $worked = "N";
  }

  //total time worked for the day
  $totalTime = getTimeWorked($date);
  

  //average time per entry
  $sql = "SELECT SUM(minutes) FROM `entries` WHERE start_time LIKE '%" . $date . "%';";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  while ($row = mysqli_fetch_array($result)) {
    $sumMinutes = $row['SUM(minutes)'];
  }
  if ($worked == "Y"){
    $averageTimeSpent = $sumMinutes / $numberEntries;
  } else {
    $averageTimeSpent = 0;
  }

  //count number of interrupts
  $sql = "SELECT COUNT(start_time) FROM `entries` WHERE start_time LIKE '%" . $date . "%' AND interrupted LIKE 'Y'";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  while ($row = mysqli_fetch_array($result)) {
    $numberInterrupts = $row['COUNT(start_time)'];
  }




  $dayTotal = [
    'date' => $date,
    'day' => $dateDay,
    'worked' => $worked,
    'timeWorked' => $totalTime,
    'numberEntries' => $numberEntries,
    'sumMinutes' => $sumMinutes,
    'averageTimeSpent' => $averageTimeSpent,
    'numberInterrupts' => $numberInterrupts
  ];

  //add it all to the $totalsReport array
    array_push($totalsReport, $dayTotal);

}


//variables for period summary
$daysInPeriod = 0;
$daysWorked = 0;
$timeWorked = 0;
$timeTracked = 0;
$numberEntries = 0;
$numberInterrupts = 0;


//table for summary by day 
$dailyOverviewTable = '<table><tr onclick=tableToCSV(this)>
<th>Date</th>
<th>Day</th>
<th>Time Worked</th>
<th>Time Tracked</th>
<th>Number of Entries</th>
<th>Average Time spent on entry</th>
<th>Number of Times interrupted</th>
</tr>
';

foreach ($totalsReport as $day) {

//get the information for the period summary
  $daysInPeriod ++;
    
  if ($day['worked'] == 'Y'){
    $daysWorked ++;
  }
  $timeWorked += $day['timeWorked'];
  $timeTracked += $day['sumMinutes'];
  $numberEntries += $day['numberEntries'];
  $numberInterrupts += $day['numberInterrupts'];




  if ($day['worked'] == 'Y'){
    $class = "worked";
  } else {
    $class = "notWorked";
  }


  //generate table for daily summary
  $dailyOverviewTable .= "<tr class=" . $class ." onclick='newWindow(`all_entries.php?start_date=" . $day['date'] . "&end_date=" . $day['date'] . "`)'>
  <td>" . $day['date'] . "</td>
  <td>" . $day['day'] . "</td>
  <td>" . minutesToHours($day['timeWorked']) . "</td>
  <td>" . minutesToHours($day['sumMinutes']) . "</td>
  <td>" . $day['numberEntries'] . "</td>
  <td>" . minutesToHours($day['averageTimeSpent']) . "</td>
  <td>" . $day['numberInterrupts'] . "</td>
  </tr>";

}


$dailyOverviewTable .= "</table>";



//generate a summary table by category
$categories = [];
$sql = "SELECT id, display_name FROM categories ORDER BY seq ASC";
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");
while ($row = mysqli_fetch_array($result)) {
  $category['id'] = $row['id'];
  $category['displayName'] = $row['display_name'];
  array_push($categories, $category);
}


$categoryTable = "<table id=summary><tr onclick=tableToCSV(this)>
  <th>Category</th>
  <th>Time Spent</th>
  <th>Number Of Entries</th>
  <th>Average Length Of Entry</th>
  <th>Number of Interrupts</th>
  </tr>";
foreach ($categories as $category) {

$sql = "SELECT SUM(`minutes`) as Sum, COUNT(id) as Count
FROM entries 
WHERE categories_id = " . $category['id'] . "
AND start_time >= '" . $start_dateTime . "'
AND start_time <= '" . $end_dateTime . "'";
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");
while ($row = mysqli_fetch_array($result)) {
  $catMinutes = $row['Sum'];
  $catNumEntries = $row['Count'];
}

$sql = "SELECT COUNT(id) as Count
FROM entries 
WHERE categories_id = " . $category['id'] . "
AND interrupted LIKE 'Y'
AND start_time >= '" . $start_dateTime . "'
AND start_time <= '" . $end_dateTime . "'";
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");
while ($row = mysqli_fetch_array($result)) {
  $catNuminterrupts = $row['Count'];
}


if ($catMinutes != "") {
  $categoryTable .= "<tr onclick='newWindow(`all_entries.php?start_date=" . $start_date .  "&end_date=" . $end_date . "&category=" . $category['id'] . "`)' ><td>" . $category['displayName'] . "</td>
    <td>" . minutesToHours($catMinutes) . "</td>
    <td>" . $catNumEntries . "</td>
    <td>" . minutesToHours(calcAverage($catMinutes, $catNumEntries)) . "</td>
    <td>" . $catNuminterrupts . "</td>
    </tr>";
}
}


$categoryTable .= "</table>";



//generate a summary table by Tag
$tags = [];
$sql = "SELECT id, tag
  FROM tags
  ORDER BY tag asc";
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");
while ($row = mysqli_fetch_array($result)) {
  array_push($tags, $row['tag']);
}


$tagsTable = "<table id=tagSummary><tr onclick=tableToCSV(this)>
  <th>Tag</th>
  <th>Time Spent</th>
  <th>Number Of Entries</th>
  <th>Average Length Of Entry</th>
  <th>Number of Interrupts</th>
  </tr>";
foreach ($tags as $tag) {

$sql = "SELECT SUM(`minutes`) as Sum, COUNT(id) as Count
FROM entries 
WHERE tags LIKE '%|" . $tag . "|%'
AND start_time >= '" . $start_dateTime . "'
AND start_time <= '" . $end_dateTime . "'";
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");
while ($row = mysqli_fetch_array($result)) {
  $tagMinutes = $row['Sum'];
  $tagNumEntries = $row['Count'];
}

$sql = "SELECT COUNT(id) as Count
FROM entries 
WHERE tags LIKE '%|" . $tag . "|%'
AND interrupted LIKE 'Y'
AND start_time >= '" . $start_dateTime . "'
AND start_time <= '" . $end_dateTime . "'";
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");
while ($row = mysqli_fetch_array($result)) {
  $tagNuminterrupts = $row['Count'];
}


if ($tagMinutes != "") {
  $tagsTable .= "<tr onclick='newWindow(`all_entries.php?start_date=" . $start_date .  "&end_date=" . $end_date . "&tags=|" . $tag . "|`)' ><td>" . $tag . "</td>
    <td>" . minutesToHours($tagMinutes) . "</td>
    <td>" . $tagNumEntries . "</td>
    <td>" . minutesToHours(calcAverage($tagMinutes, $tagNumEntries)) . "</td>
    <td>" . $tagNuminterrupts . "</td>
    </tr>";
}
}


$tagsTable .= "</table>";



//generate full table







?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $_SESSION['settings']['name']['value']; ?> - Employer Report</title>

  <link rel='stylesheet' href='../styles/styles.css'>
  <script src='../js/functions.js'></script>
</head>
<body>
  <?php
  include "../partials/nav.php";
  ?>
  <h2>Employer Report <?php echo $start_date . " to " . $end_date; ?></h2>


  <table id='periodReport'>
  <tr onclick=tableToCSV(this)>
    <th colspan=2>Period Report</th>
  </tr>
  <tr>
    <th>Total Days in Period</th>
    <td><?php echo $daysInPeriod; ?></td>
  </tr>
  <tr>
    <th>Total Days Worked in Period</th>
    <td><?php echo $daysWorked; ?></td>
  </tr>
  <tr>
    <th>Total Time Worked</th>
    <td><?php echo minutesToHours($timeWorked); ?></td>
  </tr>
  <tr>
    <th>Average Time Worked Per Worked Day</th>
    <td><?php echo minutesToHours(calcAverage($timeWorked,$daysWorked)); ?></td>
  </tr>
  <tr>
    <th>Total Time Tracked</th>
    <td><?php echo minutesToHours($timeTracked); ?></td>
  </tr>
  <tr>
    <th>Average Time Tracked Per Worked Day</th>
    <td><?php echo minutesToHours(calcAverage($timeTracked, $daysWorked)); ?></td>
  </tr>
  <tr>
    <th>Total number of Entries</th>
    <td><?php echo $numberEntries; ?></td>
  </tr>
  <tr>
    <th>Average Number of Entires Per Worked Day</th>
    <td><?php echo calcAverage($numberEntries,$daysWorked); ?></td>
  </tr>
  <tr>
    <th>Total Number of Interrupts</th>
    <td><?php echo $numberInterrupts; ?></td>
  </tr>
  <tr>
    <th>Average Number of Interrupts Per Worked Day</th>
    <td><?php echo calcAverage($numberInterrupts,$daysWorked); ?></td>
  </tr>
</table>

  <h4>Category Summary</h4>
  <div><?php echo $categoryTable; ?></div>

  <h4>Tag Summary</h4>
  <div><?php echo $tagsTable; ?></div>
  
  <h4>Daily Overview</h4>
  <div><?php echo $dailyOverviewTable; ?></div>



  <br><br>
  <h4>Full List of Entries</h4>
  <?php echo showEntriesTable($start_dateTime, $end_dateTime, "all"); ?>
</body>
</html>




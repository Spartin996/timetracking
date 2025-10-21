<?php
//Report page to provide summary to a report page

// V1.1 Created 2024-01-07 By MM - First version

//Get the environment settings and functions
include "../php/functions.php";
session_start();

$date_sql = date('Y-m-d', time());

$start_date = issetget("start_date", $date_sql);
$start_date = displayTime($start_date, "sql");

$end_date = issetget("end_date", $date_sql);
$end_date .= " 23:59:59";
$end_date = displayTime($end_date, "sql");

$categoriescsv = "";
if (isset($_GET['numbercats'])){
  for ($i=0; $i < $_GET['numbercats']; $i++) { 
    if (isset($_GET['categories' . $i])) {
      $categoriescsv .= $_GET['categories' . $i] . ",";
    }
  }
} elseif (isset($_GET['category'])) {
  //if you provide a single category
  $categoriescsv = $_GET['category'];
} else {
  //if you do not have a Category param at all just get all of them
  $categoriescsv = getAllCategoriesCSV();
}

$categoriescsv = rtrim($categoriescsv, ",");

$order = issetget('order', "ASC");

//get the comment search
$comment = issetget("comment", "");

//get the tags, break them up into components so that it does not matter what order they are in.
$whereTags = "";

$displayTags = "Any";
if (isset($_GET['tags']) && $_GET['tags'] != ""){
    $displayTags = $_GET['tags'];
    $tags = rtrim($_GET['tags'], ",");
    $tags = explode(",", $tags);
    foreach ($tags as $tag) {
      $whereTags .= " AND tags LIKE '%" . $tag . "%'
      ";
    }
  }
  



//build the Where clause for all SQL.
$whereParam = "
  WHERE start_time >= '" . $start_date . "'
  AND start_time <= '" . $end_date . "'
  AND categories_id IN (" . $categoriescsv . ")
  AND comment LIKE '%" . $comment . "%'"
  . $whereTags;




//generate a summary table by category
$categories = [];
$sql = "SELECT id, display_name FROM categories WHERE id IN (" . $categoriescsv . ") ORDER BY seq ASC";
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
AND start_time >= '" . $start_date . "'
AND start_time <= '" . $end_date . "'
AND comment LIKE '%" . $comment . "%'"
. $whereTags;
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
AND start_time >= '" . $start_date . "'
AND start_time <= '" . $end_date . "'
AND comment LIKE '%" . $comment . "%'"
. $whereTags;
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");
while ($row = mysqli_fetch_array($result)) {
  $catNuminterrupts = $row['Count'];
}


if ($catMinutes != "") {
  $categoryTable .= "<tr><td>" . $category['displayName'] . "</td>
    <td>" . minutesToHours($catMinutes) . "</td>
    <td>" . $catNumEntries . "</td>
    <td>" . minutesToHours(calcAverage($catMinutes, $catNumEntries)) . "</td>
    <td>" . $catNuminterrupts . "</td>
    </tr>";
}
}


$categoryTable .= "</table>";











//get the full entries table

$sql = "SELECT entries.id, categories_id, display_name, start_time, end_time, `minutes`, `interrupted`, `comment`, `tags` 
  FROM entries
  LEFT JOIN categories
  ON entries.categories_id = categories.id "
  . $whereParam . " ORDER BY start_time " . $order;

  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  $entriesTable = "<table id=showEntries><tr onclick=tableToCSV(this)><th>Category</th><th>Start Time</th><th>End Time</th><th>Time Taken</th><th>Interrupted</th><th>Comments</th><th>Tags</th></tr>";
  while ($row = mysqli_fetch_array($result)) {
    $entriesTable .= "<tr onclick='newWindow(`entries.php?id=" . $row['id'] . "`)' >
    <td>" . $row['display_name'] . "</td>
    <td>" . displayTime($row['start_time'], "12") . "</td>
    <td>" . displayTime($row['end_time'], "12") . "</td>
    <td>" . minutesToHours($row['minutes']) . "</td>
    <td>" . $row['interrupted'] . "</td>
    <td>" . $row['comment'] . "</td>
    <td>" . dislpayTags($row['tags']) . "</td>
    </tr>";
  }

  $entriesTable .= "</table>";






?>

<!DOCTYPE html>
<html>

<head>
  <title><?php echo $_SESSION['settings']['name']['value']; ?> - Edit Entry</title>
  <link rel='stylesheet' href='../styles/styles.css'>
  <script src='../js/functions.js'></script>
</head>

<body>
  <?php 
    include "../partials/nav.php";
  ?>
  <h2>Report for <?php echo $start_date ?> to <?php echo $end_date?></h2>
  <p>Report includes:</p>
    
    <p>Categories - <?php 
    $displayCats = "";
    foreach ($categories as $category) {
      $displayCats .= $category['displayName'] . ", ";
    }
      echo rtrim($displayCats, ", "); 
    ?>
    </p>
    <p>comments like - <?php 
    if($comment == "") {
      echo "Any"; 
    }
    else {
      echo $comment;
    }
    ?></p>
    <p>Tags - <?php echo dislpayTags($displayTags)?></p>

  <?php

  echo $categoryTable;
  echo $entriesTable;
  ?>

</body>

</html>
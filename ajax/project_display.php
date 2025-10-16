<?php

//file to display a list of projects


//Get the environment settings and functions
include "../php/functions.php";
include "../connect.ini";
session_start();

$data = json_decode(file_get_contents('php://input'), true);


if ($data['type'] == "standard") {
  //this is a standard search, it will get all open projects

  //generate the SQL

$sql = "SELECT p.id, `title`, c.display_name, `date_created`, `date_closed`, `minutes`, `steps`, `steps_complete` 
  FROM `projects` p 
  LEFT JOIN categories c 
    ON project_cat = c.id
  WHERE `date_closed` IS NULL;";


} else {


//this is a advanced search
  //get all the filters and options from the form
  $searchInput = $data["searchKey"];
  $searchTitle = $data["searchTitle"];
  $searchContent = $data["searchContent"];
  $includeClosed = $data["includeClosed"];
  $searchRange = $data["searchRange"];

  $startDate = $data["startDate"];
  $startDate = displayTime($startDate, "sql");
  
  $endDate = $data["endDate"];
  $endDate .= " 23:59:59";
  $endDate = displayTime($endDate, "sql");


  if ($searchTitle == true && $searchContent == true) {
    //search both title and content
    $keywordSQL = "(`title` LIKE '%" . $searchInput . "%' OR `project_desc` LIKE '%" . $searchInput . "%')";
  } elseif ($searchTitle == true) {
    //search title only
    $keywordSQL = "`title` LIKE '%" . $searchInput . "%'";
  } elseif ($searchContent == true) {
    //search content only
    $keywordSQL = "`project_desc` LIKE '%" . $searchInput . "%'";
  } else {
    //no search
    $keywordSQL = "";
  }

  if ($includeClosed == false) {
    //do not include closed projects
    $closedSQL = "`date_closed` IS NULL";
  } else {
    //include closed projects
    $closedSQL = "";
  }

  if ($searchRange == "startedIn" && $startDate != "" && $endDate != "") {
    //search for projects started in a range
    $dateSQL = "`date_created` BETWEEN '" . $startDate . "' AND '" . $endDate . "'";
  } elseif ($searchRange == "closedIn" && $startDate != "" && $endDate != "") {
    //search for projects closed in a range
    $dateSQL = "`date_closed` BETWEEN '" . $startDate . "' AND '" . $endDate . "'";
  } elseif ($searchRange == "all" && $startDate != "" && $endDate != "") {
    //search for all projects
    $dateSQL = "`date_created` Between '" . $startDate . "' AND '" . $endDate . "' OR `date_closed` BETWEEN '" . $startDate . "' AND '" . $endDate . "'";
  } else {
    //no date search
    $dateSQL = "";
  }


  $whereSQL = "WHERE ";
  if ($keywordSQL != "") {
    $whereSQL .= $keywordSQL;
  }
  //if there is a dateSQL, add it to the where
  if ($dateSQL != "") {
    if ($keywordSQL != "") {
      $whereSQL .= " AND ";
    }
    $whereSQL .= $dateSQL;
  }
  //if there is a closedSQL, add it to the where
  if ($closedSQL != "") {
    if ($keywordSQL != "" || $dateSQL != "") {
      $whereSQL .= " AND ";
    }
    $whereSQL .= $closedSQL;
  }

  //if there is no where clause, remove the where
  if ($whereSQL == "WHERE ") {
    $whereSQL = "";
  }

  //generate the SQL
  $sql = "SELECT p.id, `title`, c.display_name, `date_created`, `date_closed`, `minutes`, `steps`, `steps_complete` 
  FROM `projects` p 
  LEFT JOIN categories c 
    ON project_cat = c.id "
    . $whereSQL . ";";



}






logAction("Called from project_display.php, SQL is " . $sql, "file");
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");



//build the Table 


$table = "<table>
  <tr onclick=tableToCSV(this)>
    <th>id</th>
    <th>Date Started</th>
    <th>Date Finished</th>
    <th>Title</th>
    <th>Category</th>
    <th>TODO</th>
    <th>Time Spent</th>
  </tr>";


while ($row = mysqli_fetch_array($result)) {
  $tableRow = "<tr>";
  $tableRow .= "<td>
  <a href='project_edit.php?id=" . $row['id'] . "' class=icon>
    <svg class='icon' xmlns='http://www.w3.org/2000/svg' height='24px' viewBox='0 -960 960 960' width='24px' fill='#e8eaed'>
      <path d='M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z'/>
    </svg>
  </a>
  <span onclick='newWindow(`project_edit.php?id=" . $row['id'] . "`)'>
    <svg class='icon' xmlns='http://www.w3.org/2000/svg' height='24px' viewBox='0 -960 960 960' width='24px' fill='#e8eaed'>
      <path d='M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h240v80H200v560h560v-240h80v240q0 33-23.5 56.5T760-120H200Zm440-400v-120H520v-80h120v-120h80v120h120v80H720v120h-80Z'/>
    </svg>
  </span>
</td>";
  $tableRow .= "<td>" . displayTime($row["date_created"], setting('date_view')) . "</td>";
  $tableRow .= "<td>" . displayTime($row['date_closed'], setting('date_view')) . "</td>";
  $tableRow .= "<td>" . $row["title"] . "</td>";
  $tableRow .= "<td>" . $row["display_name"] . "</td>";
  $tableRow .= "<td>" . $row["steps_complete"] . " / " . $row["steps"] . "</td>";
  $tableRow .= "<td>" . $row["minutes"] . "</td>";
  $tableRow .= "</tr>";

  $table .= $tableRow;
}



//Return the table

echo $table;


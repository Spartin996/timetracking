<?php

//file to save projects


//Get the environment settings and functions
include "../php/functions.php";
include "../connect.ini";

$data = json_decode(file_get_contents('php://input'), true); 

$id = $data['id'];

$title = $data['title'];

$project_cat = $data['project_cat'];

$date_created = $data['dateCreated'];
  //if date created is empty set it to the current time
  if ($date_created == "") {
    $date_created = date("Y-m-d H:i:s");
  }
  //format the date to sql
  $date_created = displayTime($date_created, 'sql');

$date_closed = $data['dateClosed'];
  $date_closed = displayTime($date_closed, 'sql');

$editor_content = $data['editorContent'];

$steps = countCheckboxes($editor_content);



// if ID must be a edit
  if ($id != "") {
    
    $minutes = UpdateTimeOnProject($id);


    //update sql
    $sql = "UPDATE `projects` SET `title` = '" . $title . "', `project_cat` = '" . $project_cat . "', `date_created` = '" . $date_created . "', `date_closed` = ";
    if ($date_closed == "") {
      $sql .= "NULL";
    } else {
      $sql .= "'" . $date_closed . "'";
    }
    $sql .= ", `project_desc` = '" . $editor_content . "', `minutes` = '" . $minutes . "', `steps` = '" . $steps['total'] . "', `steps_complete` = '" . $steps['checked'] . "', `steps_incomplete` = '" . $steps['unchecked'] . "'";
    
    $sql .= " WHERE id = " . $id;
  } else {
    //No ID, insert
    $minutes = UpdateTimeOnProject($id);
    $sql = "INSERT INTO `projects` (`id`, `title`, `project_cat`, `date_created`, `date_closed`, `project_desc`, `minutes`, `steps`, `steps_complete`, `steps_incomplete`) VALUES (NULL, '" . $title . "', '" . $project_cat . "', '" . $date_created . "', ";
    if ($date_closed == "") {
      $sql .= "NULL";
    } else {
      $sql .= "'" . $date_closed . "'";
    } 
    $sql .= ", '" . $editor_content . "', '" . $minutes . "', '" . $steps['total'] . "', '" . $steps['checked'] . "', '" . $steps['unchecked'] . "')";
  }
  logAction("SQL generated, " . $sql, "file");
  $run = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");


  if ($id == "") {
    //get the ID number of the new entry, Assume that Title and date created is a unique value add a order by to get the most recent id just to be safe
    $sql = "SELECT id FROM projects 
      WHERE title LIKE '" . $title . "' 
      AND date_created LIKE '" . $date_created . "'
      ORDER BY id DESC";

      logAction("running SQL on DB, " . $sql, "file");
      $result = $conn->query($sql);
      $row = mysqli_fetch_array($result);
      $id = $row['id'];


  }

  //todo if failed show error
  echo "success id =" . $id;
  exit(); 
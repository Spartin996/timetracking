<?php

//connect to DB using a extrenal connect.ini file
require_once('../connect.ini');

?>

<html>
    <head>
        <title>Generic table delete</title>

    </head>
    <body>
      <h1>TRYING TO DELETE ENTRY</h1>
      <a href=javascript:history.back()>GO BACK</a>

<?php

if (isset($_GET['table'])){
  $table = $_GET['table'];
  } else {
    echo "YOU HAVE NOT PROVIDED THE REQUIRED VARIBLES IN THE URL <a href=javascript:history.back()>GO BACK</a>";
    EXIT;
  
  }

if (isset($_GET['ID'])){
  $id = $_GET['ID'];
} else {
  $id = NULL;
}


$delete_sql = "DELETE FROM `" . $table . "` WHERE `ID` = " . $id . ";";
if ($conn->query($delete_sql) === TRUE) {
  echo "Record Deleted successfully";
} else {
  echo "Error Deleting record: " . $conn->error;
}
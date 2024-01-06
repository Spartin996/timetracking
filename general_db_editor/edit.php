<?php

//This is designed to edit and manager a Mysql database

//the URL will need to have a Table in it
// if you are creating a entry that will be it
// if you are editing a entry it will also need a ID
//currently the table that you are edditing needs to have a auto incrimenting feild that is call id.
//eventually it will just need to be auto incrimenting
//the script is likely to break if you have multiple auto incimenting feilds but I have not tried it.
//example URL
//    http://localhost/XXXX/edit.php?ID=1&table=transactions
//to create a entry
// http://localhost/XXXX/edit.php?ID=0&table=transactions

//version 1.1

//connect to DB using a extrenal connect.ini file
require_once('../connect.ini');
require_once('functions.php');





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



?>

<html>
    <head>
        <title>Generic table editor</title>

        <link rel="stylesheet" href="../css/standard.css">

    </head>
    <body>
        <h1>Edit or Add A item to the database</h1>
        <p>If you do not see the feild that you wish to edit you will need to go the PHPMA and edit it there.</p>
        <a href=javascript:history.back()>GO BACK</a>
        <form method=GET action="edit.php">
          

<?php
//set variables

//set a submitted flag
echo "<input type=hidden name=form value=submitted>";
//send the table
echo "<input type=hidden name=table value='" . $table . "'>";


//get the table structure
$desc_sql = "DESCRIBE " . $table;
$desc = $conn->query($desc_sql);
$num_feild = 0;
while($row = mysqli_fetch_array($desc)){
      #  print_r($row);
    $col[$num_feild][] = $row['Field'];
    $col[$num_feild][]= $row['Type'];
    $col[$num_feild][] = $row['Extra'];
    $num_feild = $num_feild + 1;
}


//this checks if the form has been subbmitted and has a valide ID so this is a edit

if (isset($_GET['form']) and $id != ""){
  $update_sql = "UPDATE `" . $table . "` SET ";
    foreach($col as $get_col) {
      //print_r($get_col);
      //echo $_GET[$get_col[0]];
      //check if it is the ID
      if ($get_col[2] == "auto_increment") {
        $where = "WHERE `" . $get_col[0] . "` = '" . $_GET[$get_col[0]] . "';";
      } else {
        $update_sql .= " `" . $get_col[0] . "` = ";
        //IF feild Blank Make NULL
        if ($_GET[$get_col[0]] == ""){
          $update_sql .= "NULL, ";
        } else {
          $update_sql .= "'" . $_GET[$get_col[0]] . "', ";
        }
      }
    }

  //take the last , off the $update_sql
  $update_sql = substr($update_sql, 0, -2);
  //add the update and the Where together
  $update_sql .= " " . $where;

  echo "<BR>";
  //echo $update_sql;
  if ($conn->query($update_sql) === TRUE) {
    echo "Record updated successfully";
  } else {
    echo "Error updating record: " . $conn->error;
  }
}



//Create a new entry

if (isset($_GET['form']) and $id == ''){
  
  $insert_sql = "INSERT INTO `" . $table . "` (";
  $insert_content = "VALUES (";
    foreach($col as $get_col) {
      //print_r($get_col);
      //echo $_GET[$get_col[0]];
      //check if it is the ID
      if ($get_col[2] == "auto_increment") {
      
      } else {
        $insert_sql .= " `" . $get_col[0] . "`,";
        //IF feild Blank Make NULL
        if ($_GET[$get_col[0]] == ""){
          $insert_content .= "NULL, ";
        } else {
          $insert_content .= "'" . $_GET[$get_col[0]] . "', ";
        }
      }
    }

  //take the last , off the $update_sql
  $insert_sql = substr($insert_sql, 0, -1);
  $insert_content = substr($insert_content, 0, -2);
  //add the insert feilds and the content together.
  $insert_sql .= ") " . $insert_content . ")";
  echo "<BR>";
//  echo $insert_sql;
  if ($conn->query($insert_sql) === TRUE) {
    echo "Record inserted successfully";
  } else {
    echo "Error inserting record: " . $conn->error;
  }



}

if (isset($_GET['ID'])) {
  $sql = "SELECT * FROM " . $table;
  $sql .= " WHERE id = '" . $id . "'";
  $result = $conn->query($sql);
  $row = mysqli_fetch_array($result);
    
}
// echo $sql;

//   print_r($col);
echo "<br>";


echo "<div class=edit>
";


//this is the section that displays
// $dis_col is what to display the feild as
//        [0] => name of feild
//        [1] => type of feild
//        [2] => auto_increment / blank if not

    foreach($col as $dis_col) {
        echo "<p>";
        # print_r($dis_col);
        if ($dis_col[2] == "auto_increment"){
          //this assumes that this is the ID Of the table
            echo "<a href=delete.php?ID=" . issetor($row[$dis_col[0]]) . "&table=" . $table . ">Delete The Entire Record</a>";
            echo "<input type=hidden name='" . $dis_col[0] . "' value='" . issetor($row[$dis_col[0]]) . "'>";
        }
        
        elseif ($dis_col[1] == "varchar(1)") {
          //this assumes that it is a tickbox because it is a VARCHAR of 1
          echo "<span class=label>" . $dis_col[0] . ": </span>";
            if (issetor($row[$dis_col[0]]) == "Y") {
              echo "<input type=radio id='Y' name='" . $dis_col[0] . "' value='Y' Checked><label for='Y'>Yes</label>
                    <input type=radio id='N' name='" . $dis_col[0] . "' value='N'><label for='N'>No</label>";
            } elseif (issetor($row[$dis_col[0]]) == "N") {
              echo "<input type=radio id='Y' name='" . $dis_col[0] . "' value='Y'><label for='Y'>Yes</label>
                    <input type=radio id='N' name='" . $dis_col[0] . "' value='N' Checked><label for='N'>No</label>";
            } elseif (issetor($row[$dis_col[0]]) == "") {
              echo "<input type=radio id='Y' name='" . $dis_col[0] . "' value='Y' Checked><label for='Y'>Yes</label>
                    <input type=radio id='N' name='" . $dis_col[0] . "' value='N'><label for='N'>No</label>";
            } else {
              echo "<input type=text name='" . $dis_col[0] . "' value='" . issetor($row[$dis_col[0]]) . "'>";
            }
        }
        
        elseif ($dis_col[1] == "date") {
          //this assumes date
          echo "<label for='" . $dis_col[0] . "'>" . $dis_col[0] . ": </label>";
            echo "<input type=date name='" . $dis_col[0] . "' id='" . $dis_col[0] . "' value='" . issetor($row[$dis_col[0]]) . "'>";
        }

        elseif ($dis_col[1] == "datetime") {
          //this is date and time
          if (issetor($row[$dis_col[0]]) == "") {
            //no current date get the local time
            $date = date('Y-m-d H:i:s');

          }
          elseif ($dis_col[0] == "last_modified") {
            //if it is a last modified tracker get the local timezone
            $date = date('Y-m-d H:i:s');

          }
          else {
            //if you are editing a entry that has a date
            $date = issetor($row[$dis_col[0]]);
          }
          echo "<label for='" . $dis_col[0] . "'>" . $dis_col[0] . ": </label>";
          echo "<input type=datetime-local name='" . $dis_col[0] . "' id='" . $dis_col[0] . "'  value='" . $date . "'>";
        }

        elseif ($dis_col[1] == "longtext") {
          //this is for longtext/lots of data
          echo "<label for='" . $dis_col[0] . "'>" . $dis_col[0] . ": </label><br />";
          echo "<textarea id='" . $dis_col[0] . "' name='" . $dis_col[0] . "' rows=14 cols=70>";
            if (issetor($row[$dis_col[0]]) != "") {
              echo $row[$dis_col[0]] . "

--- Update " . date('Y-m-d H:i:s') . " ---

";

            }

          echo "</textarea>";

        }

        elseif ($dis_col[1] == "int(4)" AND substr($dis_col[0], -3) == "_id") {
          //This is if it is a foregin key, found by having _id on the end of the feild name and the table as the front part
          // for example category_id is a foreign key form a table called category.

          // get the table name
          $table_for_dropdown = substr($dis_col[0], 0, -3);
          echo "<label for='" . $dis_col[0] . "'>" . $table_for_dropdown . ": </label>" ;
          echo "<select id='" . $dis_col[0] . "' name='" . $dis_col[0] . "'>";
          gen_dropdown($table_for_dropdown);
          echo "</select>";
        }

        else {
          echo "<label for='" . $dis_col[0] . "'>" . $dis_col[0] . ": </label>";
          echo "<input type=text name='" . $dis_col[0] . "' id='" . $dis_col[0] . "' value='" . issetor($row[$dis_col[0]]) . "'>";
        }
      
        
        echo "</p>";
    }

echo "<p><input type=submit value=save></p>";
echo "</div>";


?>



</form>
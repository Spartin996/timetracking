<?php
// view a table
//this is a function to render out a table as a html table that is editable

//standard includes
require_once('../connect.ini');


//get the table name
if (isset($_GET['table'])){
  $table = $_GET['table'];
  } else {
    echo "YOU HAVE NOT PROVIDED THE REQUIRED VARIBLES IN THE URL <a href=javascript:history.back()>GO BACK</a>";
    EXIT;
  
  }


?>

<html>
    <head>
        <title>Generic table editor</title>

    </head>
    <body>
      <h1>View Table <?= $table ?> </h1>
      <p> This is a generic module to view and edit MYSQL databases.</p>
      <a href="edit.php?table=<?= $table ?>">Add a new Entry </a>
      <a href=javascript:history.back()>GO BACK</a>
<br>





<?php





//get the table structure
$desc_sql = "DESCRIBE " . $table;
$desc = $conn->query($desc_sql);
$sql = "SELECT * FROM " . $table;
$result = $conn->query($sql);
$i = 0;
while($row = mysqli_fetch_array($desc)){
#        echo $row['Field'];
    $col[$i][] = $row['Field'];
    $col[$i][]= $row['Type'];
    $col[$i][] = $row['Extra'];
    $i = $i + 1;
}
#   print_r($col);
echo "<br>";

echo "<table>";
echo "<tr>";
#column heading
foreach($col as $dis_col) {
    echo "<th>";
    echo $dis_col[0];
    echo "</th>";
}
echo "</tr>
";

while($row = mysqli_fetch_array($result)){
    echo "<tr>";
    foreach($col as $dis_col) {
        echo "<td>";
        if ($dis_col[2] == "auto_increment"){
          //this assumes it is the ID
            echo "<a href=edit.php?ID=" . $row[$dis_col[0]] . "&table=" . $table . ">Edit</a> 
            <a href=delete.php?ID=" . $row[$dis_col[0]] . "&table=" . $table . ">Delete</a>";
        } elseif ($dis_col[1] == "varchar(1)") {
          //this assumes that it is a tickbox because it is a VARCHAR of 1
            if ($row[$dis_col[0]] == "Y") {
              echo "&#x2611;";
            } elseif ($row[$dis_col[0]] == "N") {
              echo "&#x2612;";
            }else {
              echo $row[$dis_col[0]];
            }

        } else {
          echo $row[$dis_col[0]];  
        }
        echo "</td>";
    }
    echo "</tr>";

}

echo "</table>";


?>
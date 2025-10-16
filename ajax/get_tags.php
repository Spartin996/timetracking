<?php

// file to return possible tags for the JS

//Get the environment settings and functions
include "../php/functions.php";
include "../connect.ini";


//get tags
$sql = "SELECT id, tag
  FROM tags
  ORDER BY tag asc";
$result = $conn->query($sql);

$tags = array();
while ($row = mysqli_fetch_array($result)) {
  array_push($tags, $row['tag']);
}

$json = json_encode($tags);


//return them for the JS
echo $json;
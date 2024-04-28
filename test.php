<?php
require('config.php');
require('Database.php');



//dump and Die
function dd($variable) {
  echo "<pre>";
  var_dump($variable);
  echo "</pre>";
  die();
}

$db = new Database($config, $dbuser, $dbpass);





$sql = "SELECT entries.id, categories_id, display_name, start_time, end_time, comment 
FROM entries
LEFT JOIN categories
ON entries.categories_id = categories.id 
WHERE end_time IS NULL Limit 1;";


$result = $db->query($sql)->fetchAll();
dd($results);


//$row = mysqli_fetch_array($result);
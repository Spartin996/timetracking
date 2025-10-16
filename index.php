<?php

//This is the first landing page for the software.

// It will check that the db is in place and working

include 'connect.ini';


//put the settings into the session


function getSettings() {
  global $conn;
  $settings = [];
  $sql = "SELECT `id`, `setting`, `value`, `description` FROM settings";
  $result = $conn->query($sql);
//  logAction("Ran SQL on DB, " . $sql, "file");
  while ($row = mysqli_fetch_array($result)) {
    $settings[$row['setting']] = [
      "value" => $row['value'],
      "description" => $row['description']
    ];
  }

  return $settings;
}


session_start();

$_SESSION['settings'] = getSettings();









// check the settings in the db file and save relevant settings to the session


//if the required settings are not in the db, add them and then continue














// redirect to the actual index page

echo "<a href='./pages/index.php'>Go to the main page</a>";
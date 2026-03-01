<?php

//This is the first landing page for the software.

// It will check that the db is in place and working

//check for the db connection

if (!file_exists('Database.php')) {
  die("Database connection file not found. Please copy Database.php from the install directory and update the database credentials.");
} else {
  include 'Database.php';
}



//put the settings into the session

include 'php/functions.php';



session_start();

$_SESSION['settings'] = getSettings();






//if the required settings are not in the db, add them and then continue














// redirect to the actual index page

// echo "<a href='./pages/index.php'>Go to the main page</a>";

// auto redirect to the actual index page
header("Location: ./pages/index.php");
exit();
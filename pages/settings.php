<?php

//file to manage the setings of the website

//Get the environment settings and functions
include "../php/functions.php";
include "../Database.php";
//Start the session to access session variables
session_start();

check_settings();


//Form is submitted, POST variables
if (isset($_POST["hasBeenSub"])) {
  $name = issetpost("name");
  echo "SUBMITED";

    //get the settings from the form
    $new_settings = array(
      "name" => issetpost("name"),
      "header_img" => issetpost("header_img"),
      "date_view" => issetpost("date_view"),
      "primary_background" => issetpost("primary_background"),
      "secondary_background" => issetpost("secondary_background"),
      "active_background" => issetpost("active_background"),
      "neutral_white" => issetpost("neutral_white"),
      "neutral_gray" => issetpost("neutral_gray"),
      "neutral_active" => issetpost("neutral_active")
    );


    foreach ($new_settings as $key => $value) {
      //build the sql query to update the settings in the database
      $sql = "UPDATE settings SET value = '$value' WHERE setting = '$key'";
      $conn->query($sql);
      //log it
      logAction("ran update query: " . $sql);
    }

    $_SESSION['settings'] = getSettings();


}
















?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $_SESSION['settings']['name']['value']; ?> - Employer Report</title>

  <link rel='stylesheet' href='../styles/styles.css'>
  <script src='../js/functions.js'></script>
</head>
<body>
  <?php
  include "../partials/nav.php";
  ?>

  <h2>Settings</h2>

  <form method="POST" action="settings.php" id='settingsForm'>
    <input type="hidden" name="hasBeenSub" value="submitted">

    <div>
      <label for='name'>Software Name: </label>
      <input name='name' type='text' value="<?php echo $_SESSION['settings']['name']['value']; ?>">
    </div>
    
    <div>
      <label for='header_img'>Header Image Path: </label>
      <input name='header_img' type='text' value="<?php echo $_SESSION['settings']['header_img']['value']; ?>">
    </div>
    
    <div>
      <label for='date_view'>Date View Format: </label>
      <select name='date_view' id='date_view'>
        <option value="day_pretty" <?php echo ($_SESSION['settings']['date_view']['value'] == 'day_pretty') ? 'selected' : ''; ?>>Context Aware - Suggested Format - (Sat 3:45 pm)</option>
        <option value="day" <?php echo ($_SESSION['settings']['date_view']['value'] == 'day') ? 'selected' : ''; ?>>Day - (Sat 1st 3:45 PM)</option>
        <option value="html" <?php echo ($_SESSION['settings']['date_view']['value'] == 'html') ? 'selected' : ''; ?>>HTML Date - (2026-03-01T15:45)</option>
        <option value="sql" <?php echo ($_SESSION['settings']['date_view']['value'] == 'sql') ? 'selected' : ''; ?>>Sql - (2026-03-01 15:45:30)</option>
        <option value="24" <?php echo ($_SESSION['settings']['date_view']['value'] == '24') ? 'selected' : ''; ?>>24 hour - (2026-03-01 15:45)</option>
        <option value="12" <?php echo ($_SESSION['settings']['date_view']['value'] == '12') ? 'selected' : ''; ?>>12 hour - (2026-03-01 3:45 PM)</option>
      </select>
    </div>

    <h3>Theme Colors</h3>
    <p>Use the colour picker to select your preferred colors for the application theme. Save the form to update the previews.</p>
    <div>
      <label for='primary_background'>Primary Background Color: </label>
      <input name='primary_background' type='color' value="<?php echo $_SESSION['settings']['primary_background']['value']; ?>">
      <span style="background-color: <?php echo $_SESSION['settings']['primary_background']['value']; ?>; padding: 5px 10px; color: white; margin-left: 10px;">Preview</span>
    </div>
    
    <div>
      <label for='secondary_background'>Secondary Background Color: </label>
      <input name='secondary_background' type='color' value="<?php echo $_SESSION['settings']['secondary_background']['value']; ?>">
      <span style="background-color: <?php echo $_SESSION['settings']['secondary_background']['value']; ?>; padding: 5px 10px; color: white; margin-left: 10px;">Preview</span>
    </div>
    
    <div>
      <label for='active_background'>Active Background Color: </label>
      <input name='active_background' type='color' value="<?php echo $_SESSION['settings']['active_background']['value']; ?>">
      <span style="background-color: <?php echo $_SESSION['settings']['active_background']['value']; ?>; padding: 5px 10px; color: #333; margin-left: 10px;">Preview</span>
    </div>
    
    <div>
      <label for='neutral_white'>Neutral White: </label>
      <input name='neutral_white' type='color' value="<?php echo $_SESSION['settings']['neutral_white']['value']; ?>">
      <span style="background-color: <?php echo $_SESSION['settings']['neutral_white']['value']; ?>; padding: 5px 10px; color: #333; border: 1px solid #ccc; margin-left: 10px;">Preview</span>
    </div>
    
    <div>
      <label for='neutral_gray'>Neutral Gray: </label>
      <input name='neutral_gray' type='color' value="<?php echo $_SESSION['settings']['neutral_gray']['value']; ?>">
      <span style="background-color: <?php echo $_SESSION['settings']['neutral_gray']['value']; ?>; padding: 5px 10px; color: white; margin-left: 10px;">Preview</span>
    </div>
    
    <div>
      <label for='neutral_active'>Neutral Active: </label>
      <input name='neutral_active' type='color' value="<?php echo $_SESSION['settings']['neutral_active']['value']; ?>">
      <span style="background-color: <?php echo $_SESSION['settings']['neutral_active']['value']; ?>; padding: 5px 10px; color: #333; margin-left: 10px;">Preview</span>
    </div>

    <div style="margin-top: 20px;">
      <button type="submit">Save Settings</button>
    </div>

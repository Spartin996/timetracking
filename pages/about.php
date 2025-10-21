<?php
//about page and utilities 

// V1.1 Created 2024-01-06 By MM - First version



//Get the environment settings and functions
include "../php/functions.php";
session_start();

?>


<!DOCTYPE html>
<html>

<head>
  <title><?php echo $_SESSION['settings']['name']['value']; ?> - About</title>
  <link rel='stylesheet' href='../styles/styles.css'>
</head>

<body>
  <?php
  include "../partials/nav.php";
  ?>
<div class="container">
  <h2>About Time Tracking</h2>
  <p>
    Time Tracking is a lightweight PHP-based web application designed for personal time management. It provides a simple yet comprehensive solution for tracking work hours across different categories and projects with reporting capabilities.
  </p>

  <h3>Core Architecture</h3>
  <ul>
    <li><strong>Backend:</strong> PHP with MySQL database</li>
    <li><strong>Frontend:</strong> HTML, CSS, JavaScript (vanilla JS)</li>
    <li><strong>Server Environment:</strong> Designed for XAMPP or similar LAMP/WAMP stack</li>
  </ul>

  <h3>Key Features</h3>
  <ul>
    <li>Start/Stop Timer: Easily track time spent on tasks with a simple interface.</li>
    <li>Manual Entry: Add time entries manually for tasks that were not tracked.</li>
    <li>Categories & Projects: Organize time entries by categories and projects for better reporting.</li>
    <li>Tags: Add tags to entries for more granular tracking and filtering.</li>
    <li>Reporting: Generate reports based on categories, projects, and time periods.</li>
    <li>Projects: Manage multiple projects and associate time entries with them.</li>
  </ul>


  <h3>Installation & Setup</h3>
  <ol>
    <li>Download and install XAMPP or a similar LAMP/WAMP stack.</li>
    <li>Place the Time Tracking application files in the web server's root directory (e.g., htdocs for XAMPP).</li>
    <li>Configure the database connection settings in the configuration file (config.php).</li>
    <li>Create a MySQL database and import the provided SQL schema.</li>
    <li>Access the application via your web browser (e.g., http://localhost/timetracking).</li>
  </ol>



  <h3>Utilities</h3>
  <p>If you need to recalculate the times between start and finish times in the entries table you can click this link </p><p><a href='recalc_all.php' target='_blank'>Recalculate all time spent for all entries in the database.</a></p>
</div>

</body>

</html>
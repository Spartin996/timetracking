<?php


// add a copy of this file to the main directory and update it with your mysql details and timezone

// Enviroment Variables and settings

$config = [
  'host' => 'localhost',
  'port' => 3306,
  'dbname' => 'time_sheets',
  'charset' => 'UTF8mb4'
];
$dbuser = 'root';
$dbpass = 'root';



// Change the line below to your timezone!
date_default_timezone_set('Australia/Brisbane');

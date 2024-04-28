<?php
// new index page that will include a router and PDO migration.


//set up the DB connect
require_once('config.php');
require_once('Database.php');
$db = new Database($config, $dbuser, $dbpass);


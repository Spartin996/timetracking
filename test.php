<?php
require('config.php');
require('Database.php');




$sql = "SELECT id, display_name FROM categories ORDER BY seq ASC";

$db = new Database($config, $dbuser, $dbpass);

$entry = $db->query($sql)->fetchAll();

print_r($entry);

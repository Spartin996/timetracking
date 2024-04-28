<?php
require('config.php');
require('Database.php');




$sql = "SELECT id, display_name FROM categories WHERE id = :id ORDER BY seq ASC";

$db = new Database($config, $dbuser, $dbpass);

$entry = $db->query($sql, ['id' => 1])->fetchAll();

print_r($entry);

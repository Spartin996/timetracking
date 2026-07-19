<?php

include "../php/functions.php";
include "../Database.php";
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['settings']['name'])) {
  $_SESSION['settings'] = getSettings();
}

$current = settingValue('theme_mode', 'system');
$order = ['light', 'dark', 'system'];
$index = array_search($current, $order, true);
$next = $order[(($index === false ? 0 : $index) + 1) % count($order)];

$sql = "UPDATE settings SET value = '" . $conn->real_escape_string($next) . "' WHERE setting = 'theme_mode'";
$conn->query($sql);
logAction("ran update query: " . $sql, "file");

$_SESSION['settings'] = getSettings();

echo json_encode(['theme_mode' => $next]);

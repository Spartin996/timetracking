<?php

include "../php/functions.php";
include "../Database.php";
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['settings']['name'])) {
  $_SESSION['settings'] = getSettings();
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
  $data = [];
}

/**
 * Update a settings row, inserting it when missing.
 */
function upsertSetting(mysqli $conn, $setting, $value, $description)
{
  $setting = $conn->real_escape_string($setting);
  $value = $conn->real_escape_string($value);
  $description = $conn->real_escape_string($description);

  $sql = "UPDATE settings SET value = '{$value}' WHERE setting = '{$setting}'";
  $conn->query($sql);
  logAction("ran update query: " . $sql, "file");

  if ($conn->affected_rows === 0) {
    $check = $conn->query("SELECT id FROM settings WHERE setting = '{$setting}' LIMIT 1");
    if ($check && $check->num_rows === 0) {
      $insert = "INSERT INTO settings (`setting`, `value`, `description`) VALUES ('{$setting}', '{$value}', '{$description}')";
      $conn->query($insert);
      logAction("ran insert query: " . $insert, "file");
    }
  }
}

$response = [];

if (isset($data['home_view'])) {
  $view = normalizeHomeView($data['home_view']);
  upsertSetting($conn, 'home_view', $view, 'Home page layout: compact, standard, or calendar.');
  $response['home_view'] = $view;
}

if (isset($data['calendar_span'])) {
  $span = normalizeCalendarSpan($data['calendar_span']);
  upsertSetting($conn, 'calendar_span', $span, 'Calendar home span: day or week.');
  $response['calendar_span'] = $span;
}

if ($response === []) {
  http_response_code(400);
  echo json_encode(['error' => 'No valid preference provided']);
  exit;
}

$_SESSION['settings'] = getSettings();
echo json_encode($response);

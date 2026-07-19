<?php
/**
 * Refresh compact tracker + recent list HTML for home compact / calendar mini tracker.
 */

include "../php/functions.php";
include "../Database.php";
session_start();

header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['settings']['name'])) {
  $_SESSION['settings'] = getSettings();
}

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'full';

if ($mode === 'tracker') {
  echo compactTrackerMarkup();
  exit;
}

echo compactTrackerMarkup();
echo compactRecentEntries(8);

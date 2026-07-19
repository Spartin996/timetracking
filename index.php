<?php

// This is the first landing page for the software.
// It checks that the db is in place and working, then redirects to the app.

if (!file_exists(__DIR__ . '/config.php')) {
  header('Location: ./install.php');
  exit;
}

include __DIR__ . '/Database.php';

// put the settings into the session
include __DIR__ . '/php/functions.php';

session_start();

$_SESSION['settings'] = getSettings();

// auto redirect to the actual index page
header('Location: ./pages/index.php');
exit();

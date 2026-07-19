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
  $theme_mode = issetpost("theme_mode", "system");
  if (!in_array($theme_mode, ['light', 'dark', 'system'], true)) {
    $theme_mode = 'system';
  }

  //get the settings from the form
  $home_view = normalizeHomeView(issetpost("home_view", "standard"));
  $calendar_span = normalizeCalendarSpan(issetpost("calendar_span", "week"));

  $new_settings = array(
    "name" => issetpost("name"),
    "header_img" => issetpost("header_img"),
    "date_view" => issetpost("date_view"),
    "home_view" => $home_view,
    "calendar_span" => $calendar_span,
    "theme_mode" => $theme_mode,
    "primary_background" => issetpost("primary_background"),
    "secondary_background" => issetpost("secondary_background"),
    "active_background" => issetpost("active_background"),
    "neutral_white" => issetpost("neutral_white"),
    "neutral_gray" => issetpost("neutral_gray"),
    "neutral_active" => issetpost("neutral_active"),
    "page_background" => issetpost("page_background"),
    "text_color" => issetpost("text_color"),
    "border_color" => issetpost("border_color"),
    "on_primary" => issetpost("on_primary"),
    "dark_primary_background" => issetpost("dark_primary_background"),
    "dark_secondary_background" => issetpost("dark_secondary_background"),
    "dark_active_background" => issetpost("dark_active_background"),
    "dark_neutral_white" => issetpost("dark_neutral_white"),
    "dark_neutral_gray" => issetpost("dark_neutral_gray"),
    "dark_neutral_active" => issetpost("dark_neutral_active"),
    "dark_page_background" => issetpost("dark_page_background"),
    "dark_text_color" => issetpost("dark_text_color"),
    "dark_border_color" => issetpost("dark_border_color"),
    "dark_on_primary" => issetpost("dark_on_primary"),
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

function colourField($name, $label, $previewTextColor = '#333')
{
  $value = htmlspecialchars(settingValue($name, '#000000'), ENT_QUOTES, 'UTF-8');
  $labelEsc = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
  $needsBorder = in_array($name, [
    'neutral_white', 'page_background', 'on_primary',
    'dark_neutral_white', 'dark_page_background', 'dark_on_primary',
  ], true);
  $border = $needsBorder ? ' border: 1px solid #ccc;' : '';
  echo "<div>
      <label for='{$name}'>{$labelEsc}: </label>
      <input name='{$name}' id='{$name}' type='color' value=\"{$value}\">
      <span style=\"background-color: {$value}; padding: 5px 10px; color: {$previewTextColor};{$border} margin-left: 10px;\">Preview</span>
    </div>";
}

$theme_mode = settingValue('theme_mode', 'system');
$home_view = normalizeHomeView(settingValue('home_view', 'standard'));
$calendar_span = normalizeCalendarSpan(settingValue('calendar_span', 'week'));

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($_SESSION['settings']['name']['value'], ENT_QUOTES, 'UTF-8'); ?> - Settings</title>

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
      <input name='name' type='text' value="<?php echo htmlspecialchars($_SESSION['settings']['name']['value'], ENT_QUOTES, 'UTF-8'); ?>">
    </div>
    
    <div>
      <label for='header_img'>Header Image Path: </label>
      <input name='header_img' type='text' value="<?php echo htmlspecialchars($_SESSION['settings']['header_img']['value'], ENT_QUOTES, 'UTF-8'); ?>">
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

    <h3>Home View</h3>
    <p>Default layout for the home page. You can also switch from the home page toolbar.</p>
    <div>
      <label for='home_view'>Layout: </label>
      <select name='home_view' id='home_view'>
        <option value="compact" <?php echo ($home_view === 'compact') ? 'selected' : ''; ?>>Compact</option>
        <option value="standard" <?php echo ($home_view === 'standard') ? 'selected' : ''; ?>>Standard</option>
        <option value="calendar" <?php echo ($home_view === 'calendar') ? 'selected' : ''; ?>>Calendar</option>
      </select>
    </div>
    <div>
      <label for='calendar_span'>Calendar span: </label>
      <select name='calendar_span' id='calendar_span'>
        <option value="day" <?php echo ($calendar_span === 'day') ? 'selected' : ''; ?>>Day</option>
        <option value="week" <?php echo ($calendar_span === 'week') ? 'selected' : ''; ?>>Week</option>
      </select>
    </div>

    <h3>Theme Mode</h3>
    <p>Choose light, dark, or follow the operating system preference.</p>
    <div>
      <label for='theme_mode'>Mode: </label>
      <select name='theme_mode' id='theme_mode'>
        <option value="light" <?php echo ($theme_mode === 'light') ? 'selected' : ''; ?>>Light</option>
        <option value="dark" <?php echo ($theme_mode === 'dark') ? 'selected' : ''; ?>>Dark</option>
        <option value="system" <?php echo ($theme_mode === 'system') ? 'selected' : ''; ?>>System</option>
      </select>
    </div>

    <h3>Light Mode Colors</h3>
    <p>Defaults keep the warm brown palette. These apply in light mode and when the OS preference is light.</p>
    <?php
    colourField('primary_background', 'Primary Background', '#fff');
    colourField('secondary_background', 'Secondary Background', '#fff');
    colourField('active_background', 'Active Background', '#333');
    colourField('neutral_white', 'Surface', '#333');
    colourField('neutral_gray', 'Muted Gray', '#fff');
    colourField('neutral_active', 'Neutral Active', '#333');
    colourField('page_background', 'Page Background', '#333');
    colourField('text_color', 'Text Colour', '#fff');
    colourField('border_color', 'Border Colour', '#fff');
    colourField('on_primary', 'Text on Primary', '#333');
    ?>

    <h3>Dark Mode Colors</h3>
    <p>Defaults keep the same warm brand tones with darker surfaces for low-light use.</p>
    <?php
    colourField('dark_primary_background', 'Primary Background', '#fff');
    colourField('dark_secondary_background', 'Secondary Background', '#fff');
    colourField('dark_active_background', 'Active Background', '#333');
    colourField('dark_neutral_white', 'Surface', '#fff');
    colourField('dark_neutral_gray', 'Muted Gray', '#fff');
    colourField('dark_neutral_active', 'Neutral Active', '#fff');
    colourField('dark_page_background', 'Page Background', '#fff');
    colourField('dark_text_color', 'Text Colour', '#333');
    colourField('dark_border_color', 'Border Colour', '#fff');
    colourField('dark_on_primary', 'Text on Primary', '#333');
    ?>

    <div style="margin-top: 20px;">
      <button type="submit">Save Settings</button>
    </div>
  </form>
</body>
</html>

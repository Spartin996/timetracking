<?php
//Functions for the rest of the util

// V1.1 Created 2024-01-06 By MM - First version


//get the environment settings and functions
include __DIR__ . '/../Database.php';


//dump and Die
function dd($variable) {
  echo "<pre>";
  var_dump($variable);
  echo "</pre>";
  die();
}

/**
 * Quill CDN assets (safe to call more than once per request).
 */
function quillAssets()
{
  static $included = false;
  if ($included) {
    return '';
  }
  $included = true;
  return '<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />'
    . '<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>';
}

/**
 * Compact Quill editor markup with a hidden input for form / AJAX sync.
 *
 * @param string $name Hidden input name/id
 * @param string $content Initial HTML
 * @param string|null $editorId Optional editor element id
 */
function quillEditorMarkup($name, $content = '', $editorId = null)
{
  $editorId = $editorId ?: ($name . '-editor');
  return '<div class="quill-compact-wrap">'
    . '<div class="quill-editor" id="' . htmlspecialchars($editorId, ENT_QUOTES, 'UTF-8') . '"'
    . ' data-quill-target="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">'
    . $content
    . '</div>'
    . '<input type="hidden" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"'
    . ' id="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="">'
    . '</div>';
}


/**
 * Whitelisted return page after start/stop.
 */
function safeReturnLocation($fallback = 'index.php')
{
  $allowed = ['index.php', 'split.php'];
  $page = basename((string) issetrequest('return_to', $fallback));
  if (!in_array($page, $allowed, true)) {
    $page = $fallback;
  }
  if ($page === 'split.php') {
    $project = issetrequest('return_project', '');
    if (preg_match('/^\d+$/', (string) $project)) {
      return 'split.php?project=' . $project;
    }
  }
  return $page;
}

//Generate the main div for index.php
//todo move this to a new file
function startStopForm($returnTo = null, $projectId = null)
{
//it will show either the current job with a stop button
//or a list of jobs and a start button

  global $conn;

  $hidden = '';
  if ($returnTo) {
    $returnEsc = htmlspecialchars((string) $returnTo, ENT_QUOTES, 'UTF-8');
    $projectEsc = ($projectId !== null && $projectId !== '' && preg_match('/^\d+$/', (string) $projectId))
      ? htmlspecialchars((string) (int) $projectId, ENT_QUOTES, 'UTF-8')
      : '';
    $hidden = '<input type="hidden" name="return_to" value="' . $returnEsc . '">'
      . '<input type="hidden" name="return_project" id="return_project" value="' . $projectEsc . '">';
  }

  //see if user is currently working
  $sql = "SELECT entries.id, categories_id, display_name, start_time, end_time, comment, tags, project_id, follow_up
  FROM entries
  LEFT JOIN categories
  ON entries.categories_id = categories.id 
  WHERE end_time IS NULL Limit 1;";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  $row = mysqli_fetch_array($result);
  if ($row) {
    //if you are currently working on XX this will happen
    $tagsEsc = htmlspecialchars((string) $row['tags'], ENT_QUOTES, 'UTF-8');
    $linkedProject = $row['project_id'] !== null && $row['project_id'] !== ''
      ? $row['project_id']
      : $projectId;
    $followUp = (isset($row['follow_up']) && $row['follow_up'] === 'Y') ? 'Y' : 'N';
    return "<span>Your current open Job is " . $row['display_name'] . ".</span><br><span>The Job has been open for <span id='timer'></span></span><br><form method=POST action=stop_work.php><br>"
      . quillEditorMarkup('comment', $row['comment']) . "<br>"
      . "<div class='start-stop-field'><label for='categories'>Switch to</label> " . CategoryDropList('entries', 'N') . "</div>"
      . startStopLinkFields($linkedProject, $followUp, true)
      . "<div class='tags'>
      <div>
        <label>Add Tags: </label>
        <input type='text' name='addTags' id='addTags' onkeyup='showTags(this.value)'>
      </div>
      <div id='divTags'></div>
      <input type='hidden' name='tags' id='tags' value='" . $tagsEsc . "'>
      <div id='displayTags'>

      </div>
    </div>
" . $hidden . "<input type=submit value='Stop Work'></form>";
  } else {
    //if you are not currently working on anything this will happen
    return "<span>No current open job</span><br><span>Open a Job?</span> <br>Current time:<span id='timer'></span><br> <form method=POST action=start_work.php>"
      . "<div class='start-stop-field'><label for='categories'>Category</label> " . CategoryDropList('entries', 'N') . "</div>"
      . quillEditorMarkup('comment')
      . startStopLinkFields($projectId, 'N', false)
      . $hidden . "<input type=submit value='Get To Work'></form>";
  }
}

/**
 * Project + follow-up (and optional interrupted) fields for start/stop.
 */
function startStopLinkFields($linkedProjectId = null, $followUp = 'N', $includeInterrupted = false)
{
  $followChecked = ($followUp === 'Y') ? ' checked' : '';
  $html = '';
  if ($includeInterrupted) {
    $html .= "<div class='start-stop-field'><label for='interrupted'>"
      . "<input name='interrupted' id='interrupted' type='checkbox' value='Y'>"
      . " Interrupted</label></div>";
  }
  $html .= "<div class='start-stop-field'><label for='follow_up'>"
    . "<input name='follow_up' id='follow_up' type='checkbox' value='Y'{$followChecked}>"
    . " Needs follow-up</label></div>";
  $html .= "<div class='start-stop-field'><label for='project_id'>Project</label> "
    . generateProjectsList('project_id', $linkedProjectId) . "</div>";
  return $html;
}



//get a droplist of jobs
function CategoryDropList($type, $incInactive, $default = NULL)
{
  //$incInactive will either be Y this will show all jobs
  //or N will only show active jobs
  //default will hold the category id that you want selected
  
  //check if I should inc inactive jobs
  if ($incInactive != "Y") {
    $where = "WHERE active LIKE 'Y' ";
  } else {
    $where = "";
  }

  if ($where != "") {
    $where .= "AND ";
  } else{
    $where .= "WHERE ";
  }

if ($type == "entries" || $type == "curEntries") {
    $where .= "entries LIKE 'Y'";
  } else if ($type == "projects") {
    $where .= "projects LIKE 'Y'";
  }


  global $conn;
  //see if user is currently working
  $sql = "SELECT id, display_name FROM categories " . $where . " ORDER BY seq ASC";
  logAction("CategoryDropList about to run SQL on DB, " . $sql, "file");
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  if ($type == "projects") {
    $output = "<select name='projects' id='project_cat'>";
      //add a unknown option so I do not need to include it in the SQL
    $output .= "<option value='0'>Unknown</option>";
  } else if ($type == "curEntries") {
    $output = "<select name='curEntryCat' id='curEntryCat'>";
  } else {
    $output = "<select name='categories' id='categories'>";
  }



  while ($row = mysqli_fetch_array($result)) {
    //check if we need to set a default value on the dropbox
    if ($default != NULL and $row['id'] == $default) {
      $selected = " selected=selected";
    } else {
      $selected = "";
    }

    $output .= "<option Value='" . $row['id'] . "' " . $selected . ">" . $row['display_name'] . "</option>";
  }
  $output .= "</select>";
  return $output;
}




/**
 * Inclusive SQL datetime bound. Date-only values become start or end of day.
 */
function normalizeSqlDateTime($value, $endOfDay = false)
{
  $value = trim((string) $value);
  if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
    return $endOfDay ? ($value . ' 23:59:59') : ($value . ' 00:00:00');
  }
  return $value;
}

function normalizeEntryOrder($order)
{
  return (strtolower((string) $order) === 'desc') ? 'DESC' : 'ASC';
}

/**
 * Load entries for a datetime range in one query.
 *
 * @return array<int, array>
 */
function fetchEntries($dateStart, $dateEnd, $categories = 'all', $order = 'asc')
{
  global $conn;
  $dateStart = $conn->real_escape_string(normalizeSqlDateTime($dateStart, false));
  $dateEnd = $conn->real_escape_string(normalizeSqlDateTime($dateEnd, true));
  $orderSql = normalizeEntryOrder($order);

  $categorySql = '';
  if ($categories !== 'all' && $categories !== null && $categories !== '') {
    $ids = implode(',', array_filter(array_map('intval', explode(',', rtrim((string) $categories, ',')))));
    if ($ids === '') {
      return [];
    }
    $categorySql = ' AND categories_id IN (' . $ids . ')';
  }

  $sql = "SELECT entries.id, categories_id, display_name, categories.seq, start_time, end_time,
      entries.minutes, interrupted, follow_up, comment, tags, project_id,
      projects.title, projects.title AS project_title
    FROM entries
    LEFT JOIN categories ON entries.categories_id = categories.id
    LEFT JOIN projects ON entries.project_id = projects.id
    WHERE start_time >= '" . $dateStart . "'
      AND start_time <= '" . $dateEnd . "'"
    . $categorySql . "
    ORDER BY start_time " . $orderSql;

  $result = $conn->query($sql);
  logAction("Ran SQL on DB from fetchEntries, " . $sql, "file");

  $rows = [];
  if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
      $rows[] = $row;
    }
  }
  return $rows;
}

/**
 * Keep entries whose start_time falls in an inclusive datetime range.
 *
 * @param array<int, array> $entries
 * @return array<int, array>
 */
function entriesInRange(array $entries, $dateStart, $dateEnd)
{
  $dateStart = normalizeSqlDateTime($dateStart, false);
  $dateEnd = normalizeSqlDateTime($dateEnd, true);
  $matched = [];
  foreach ($entries as $row) {
    $start = $row['start_time'] ?? '';
    if ($start >= $dateStart && $start <= $dateEnd) {
      $matched[] = $row;
    }
  }
  return $matched;
}

function entryProjectTitle(array $row)
{
  if (isset($row['title']) && $row['title'] !== '') {
    return $row['title'];
  }
  return $row['project_title'] ?? '';
}

/**
 * Summary table from already-fetched entry rows (no extra SQL).
 *
 * @param array<int, array> $entries
 */
function renderEntriesSummary(array $entries, $dateStart, $dateEnd)
{
  $table = "<table id=summary><tr onclick=tableToCSV(this)><th>Category</th><th>Time Spent</th></tr>";

  $byCategory = [];
  $allMinutes = 0;
  $interrupted = 0;
  $minStart = null;
  $maxEnd = null;

  foreach ($entries as $row) {
    $catId = (int) ($row['categories_id'] ?? 0);
    if (!isset($byCategory[$catId])) {
      $byCategory[$catId] = [
        'display_name' => $row['display_name'] ?? '',
        'minutes' => 0,
        'seq' => (int) ($row['seq'] ?? 0),
      ];
    }
    $minutes = (int) ($row['minutes'] ?? 0);
    $byCategory[$catId]['minutes'] += $minutes;
    $allMinutes += $minutes;
    if (($row['interrupted'] ?? '') === 'Y') {
      $interrupted++;
    }
    $start = $row['start_time'] ?? null;
    if ($start && ($minStart === null || $start < $minStart)) {
      $minStart = $start;
    }
    $end = $row['end_time'] ?? null;
    if ($end && ($maxEnd === null || $end > $maxEnd)) {
      $maxEnd = $end;
    }
  }

  uasort($byCategory, function ($a, $b) {
    return $a['seq'] <=> $b['seq'];
  });

  foreach ($byCategory as $category) {
    $table .= "<tr><td>" . $category['display_name'] . "</td><td>" . minutesToHours($category['minutes']) . "</td></tr>";
  }

  if ($entries !== []) {
    $table .= "<tr><td><strong>ALL JOBS</strong></td><td>" . minutesToHours($allMinutes) . "</td></tr>";
  }

  $dayStart = substr(normalizeSqlDateTime($dateStart, false), 0, 10);
  $dayEnd = substr(normalizeSqlDateTime($dateEnd, true), 0, 10);
  if ($dayStart === $dayEnd) {
    if (!$minStart) {
      $minStart = date('Y-m-d H:i:s');
    }
    if (!$maxEnd) {
      $maxEnd = date('Y-m-d H:i:s');
    }
    $totalTime = (strtotime($maxEnd) - strtotime($minStart)) / 60;
    $untracked = $totalTime - $allMinutes;
    $table .= "<tr><td>Untracked</td><td>" . minutesToHours($untracked) . "</td></tr>";
  }

  $table .= "<tr><td><strong>Interrupted</strong></td><td>" . $interrupted . "</td></tr>";
  $table .= "</table>";
  return $table;
}

/**
 * Full entries table from already-fetched rows (no extra SQL).
 *
 * @param array<int, array> $entries
 */
function renderEntriesTable(array $entries)
{
  $table = "<table id=showEntries><tr onclick=tableToCSV(this)><th>Category</th><th>Start Time</th><th>End Time</th><th>Time Taken</th><th>Interrupted</th><th>Comments</th><th>Tags</th><th>Project</th></tr>";
  foreach ($entries as $row) {
    $table .= "<tr onclick='newWindow(`entries.php?id=" . $row['id'] . "`)' >
    <td>" . $row['display_name'] . "</td>
    <td>" . displayTime($row['start_time'], setting('date_view')) . "</td>
    <td>" . displayTime($row['end_time'], setting('date_view')) . "</td>
    <td>" . minutesToHours($row['minutes']) . "</td>
    <td>" . $row['interrupted'] . "</td>
    <td>" . $row['comment'] . "</td>
    <td>" . dislpayTags($row['tags']) . "</td>
    <td>" . entryProjectTitle($row) . "</td>
    </tr>";
  }
  $table .= "</table>";
  return $table;
}

function showEntriesFromRows(array $entries, $dateStart, $dateEnd)
{
  return renderEntriesSummary($entries, $dateStart, $dateEnd) . renderEntriesTable($entries);
}

//function to show all entries for a period for a category in a table
function showEntriesTable($dateStart, $dateEnd, $categories, $order = "asc")
{
  return renderEntriesTable(fetchEntries($dateStart, $dateEnd, $categories, $order));
}


//function to get yesterdays date
function getOldDate($goBack)
{
  $goBack = "-" . $goBack . " days";
  return date_create()->modify($goBack)->format('Y-m-d');
}

//recalc all minutes fields in all entries
function recalcAllMinutes()
{
  global $conn;
  $sql = "SELECT id, start_time, end_time FROM entries";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  while ($row = mysqli_fetch_array($result)) {
    $entryId = $row['id'];
    //calculate the mintues on a job
    $timespent = timeBetween($row['end_time'], $row['start_time']);

    $sql = "UPDATE entries SET `minutes` = '" . $timespent . "' WHERE id = " . $entryId;
    $update = $conn->query($sql);
    logAction("Ran SQL on DB, " . $sql, "file");
  }

  return "ALL Entries Recalculated";
}

//take minutes and convert to hours and minutes
function minutesToHours($minutes)
{
  //if minutes is empty return 0:00
  if ($minutes == "") {
    return "0:00";
  }

  $hours = floor($minutes / 60);
  $min = $minutes - ($hours * 60);
  $min = floor($min);
  if ($min < 10) {
    $min = "0" . $min;
  }
    return $hours . ":" . $min;
}


//get all categories
function getAllCategoriesCSV() {
  global $conn;
  $categories = "";
  $sql = "SELECT id FROM categories ORDER BY seq ASC";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  while ($row = mysqli_fetch_array($result)) {

    $categories .= $row['id'] . ",";
  }
  $categories = rtrim($categories, ",");

  return $categories;
}


//Generate a summary table
function showEntriesSummary($dateStart, $dateEnd, $categories)
{
  return renderEntriesSummary(fetchEntries($dateStart, $dateEnd, $categories, 'asc'), $dateStart, $dateEnd);
}

//show a summary table and a full table
//categories = all will get all categories and generate the reports
function showEntries($dateStart, $dateEnd, $categories, $order = "asc")
{
  $entries = fetchEntries($dateStart, $dateEnd, $categories, $order);
  return showEntriesFromRows($entries, $dateStart, $dateEnd);
}

//if a open job exists get the unix time for it
function openJob()
{
  global $conn;
  //see if user is currently working
  $sql = "SELECT entries.id, categories_id, display_name, start_time, end_time, comment 
  FROM entries
  LEFT JOIN categories
  ON entries.categories_id = categories.id 
  WHERE end_time IS NULL Limit 1;";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  $row = mysqli_fetch_array($result);
  if ($row == "") {
    return "No Open Job";
  } else {
    return strtotime($row['start_time']);
  }
}

//function to set a variable to value if it does not exist in a get
function issetget($var, $default = null)
{
  if (isset($_GET[$var]) && $_GET[$var] != "" && $_GET[$var] != NULL) {
    return $_GET[$var];
  } else {
    return $default;
  }
}

//function to set a variable to value if it does not exist in a post
function issetpost($var, $default = null)
{
  if (isset($_POST[$var]) && $_POST[$var] != "" && $_POST[$var] != NULL) {
    return $_POST[$var];
  } else {
    return $default;
  }
}

/** Prefer POST, then GET, then default. */
function issetrequest($var, $default = null)
{
  if (isset($_POST[$var]) && $_POST[$var] != "" && $_POST[$var] != NULL) {
    return $_POST[$var];
  }
  if (isset($_GET[$var]) && $_GET[$var] != "" && $_GET[$var] != NULL) {
    return $_GET[$var];
  }
  return $default;
}

//validate a date from the db as either am,pm or for a input
//supported formats
//12 - 12 hour
//24 - 24 hour
//sql - sql format
//html - html format for the value of a datetime fields
//day - day date and 12 hour
//day_pretty - same as day but will not show day if today
function displayTime($time, $format)
{
  //if entries ongoing show it
  if ($time == NULL && $format == "sql") {
    return NULL;
  }
  if ($time == NULL) {
    return "<span id='ongoing'>ONGOING</span>";
  }
  $unix = strtotime($time);
  if ($format == "12") {
    return date('Y-m-d g:i a', $unix);
  }
  if ($format == "24") {
    return date("Y-m-d H:i", $unix);
  }
  if ($format == "sql") {
    return date("Y-m-d H:i:s", $unix);
  }
  if ($format == "html") {
    $return = date("Y-m-d", $unix) . "T" . date("H:i", $unix);
    return $return;
  }
  if ($format == "day") {
    return date('D jS g:i a', $unix);
  }
  if ($format == "day_pretty") {
    //check if it is today and if so do not show the day
    //check if it is this week and if so do not show the date
    $today = date('Y-m-d');
    $date = date('Y-m-d', $unix);
    $mostRecentSunday = date('Y-m-d', strtotime('last Sunday'));
    if ($today == $date) {
      return date('g:i a', $unix);
    } elseif ($date > $mostRecentSunday) {
      return date('D g:i a', $unix);
    } else {
      return date('D jS g:i a', $unix);
    }
  }
}

function timeBetween($end_time, $start_time)
{
  $timespent = strtotime($end_time) - strtotime($start_time);
  //divide by 60 to get minutes and floor it to make it a whole number
  $timespent = floor($timespent / 60);
  //if it s less than 0 make it 1 so that you always do at least a minutes work
  if ($timespent <= 0) {
    $timespent = 1;
  }
  return $timespent;
}

/**
 * Whether verbose logging (SQL and actions) is enabled.
 * Defaults to off when the setting is missing or the session is not loaded.
 */
function loggingEnabled()
{
  $value = settingValue('log_all', 'N');
  $value = strtoupper((string) $value);
  return $value === 'Y' || $value === 'YES' || $value === 'ON' || $value === '1';
}

//function to log the action that is taken including any errors.
function logAction($var, $mode = 'both')
{
  if (!loggingEnabled()) {
    return;
  }

  if ($mode != 'file'){
    $var = htmlspecialchars($var, ENT_QUOTES);
    echo "<script>console.log('" . $var . "'); </script>";
  }
  if ($mode != 'console'){
    $logDir = __DIR__ . '/../log';
    if (!is_dir($logDir)) {
      mkdir($logDir, 0755, true);
    }
    $logfile = fopen($logDir . '/logfile.log', 'a');
    if ($logfile === false) {
      return;
    }
    $date = date('F j, Y, G:i:s');
    fwrite($logfile, '****' . $date);
    fwrite($logfile, PHP_EOL . $var . PHP_EOL . PHP_EOL);
    fclose($logfile);
  }
}

//function to display a tag
function dislpayTags($tags) {
  $tags = str_replace("|", "", (string) ($tags ?? ''));
  $tags = rtrim($tags, ",");

  return $tags;
}


//Get total time worked for a single day.
//This is calculated as earliest start_time - last end_time
function getTimeWorked($date){
  global $conn;
  $sql = "SELECT MIN(start_time), MAX(end_time)  FROM `entries` WHERE `start_time` LIKE '%" . substr($date,0, 10) . "%'";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  while ($row = mysqli_fetch_array($result)) {
    $minTime = $row['MIN(start_time)'];
    $maxTime = $row['MAX(end_time)'];
  }

  if (!$minTime){
    $minTime = date('Y-m-d H:i:s', time());
  }
  if (!$maxTime){
    $maxTime = date('Y-m-d H:i:s', time());
  }
  
  $totalTime = (strtotime($maxTime)-strtotime($minTime)) / 60;

  return $totalTime;
  
}


function calcAverage($val1, $val2) {
  if ($val1 == 0 || $val2 == 0) {
    return "0";
    logAction("Unable to calc average of " . $val1 . " and " . $val2);
  }
  
  
  return $val1/$val2;
}


//function to get a datalist of projects
function generateProjectsList($name, $default = NULL, $incClosed = "N") {
  //incClosed will either be Y, or N to include closed projects
  //default will hold the project id that you want selected to start with
  //name will be the name of the datalist and the ID of the input field

  global $conn;
  
  //sql to get all projects
  if ($incClosed == "Y") {
    $sql = "SELECT id, title, date_created FROM projects ORDER BY date_created ASC";
  } else {
    $sql = "SELECT id, title, date_created FROM projects WHERE date_closed IS NULL ORDER BY date_created ASC";
  }

  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");

 //make the select dropdown
 $output = "<select name='" . $name . "' id='" . $name . "'>";
 $output .= "<option value=''>Select a Project</option>";

  //add a create new project option
  $output .= "<option value='new'>Create New Project</option>";
  
 while ($row = mysqli_fetch_array($result)) {
   //check if we need to set a default value on the dropdown
  if ($default != NULL and $row['id'] == $default) {
    $selected = " selected=selected";
  } else {
    $selected = "";
  }
   $output .= "<option value='" . $row['id'] . "' " . $selected . ">" . $row['title'] . "</option>";
 }
 
 $output .= "</select>";

  //return it
  return $output;
}

/**
 * Create a project from a time entry (dropdown value "new").
 *
 * @return int New project id, or 0 on failure
 */
function createProjectFromEntry($title, $description = '', $categoryId = 0)
{
  global $conn;
  $titleEsc = $conn->real_escape_string((string) $title);
  $descEsc = $conn->real_escape_string((string) $description);
  $cat = (int) $categoryId;
  $now = date('Y-m-d H:i:s');
  $sql = "INSERT INTO `projects` (`id`, `title`, `project_cat`, `date_created`, `date_closed`, `project_desc`, `minutes`, `steps`, `steps_complete`, `steps_incomplete`)
    VALUES (NULL, '{$titleEsc}', '{$cat}', '{$now}', NULL, '{$descEsc}', '0', '0', '0', '0')";
  $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  return (int) $conn->insert_id;
}

/**
 * Posted project_id: empty, existing id, or "new" (creates a project).
 *
 * @return int|null
 */
function resolvePostedProjectId($comment = '', $categoryId = '')
{
  global $conn;
  $projectId = issetrequest('project_id', '');
  if ($projectId === null || $projectId === '' || $projectId === 'NULL') {
    return null;
  }
  if ($projectId === 'new') {
    $catName = 'entry';
    if ($categoryId !== '' && $categoryId !== null) {
      $cid = (int) $categoryId;
      $result = $conn->query("SELECT display_name FROM categories WHERE id = {$cid} LIMIT 1");
      if ($result && ($row = mysqli_fetch_assoc($result))) {
        $catName = $row['display_name'];
      }
    }
    $id = createProjectFromEntry('TODO - ' . $catName, $comment, $categoryId);
    return $id > 0 ? $id : null;
  }
  if (!preg_match('/^\d+$/', (string) $projectId)) {
    return null;
  }
  return (int) $projectId;
}

/**
 * Checkbox POST: Y if checked, otherwise N.
 */
function postedCheckboxY($name)
{
  $val = issetrequest($name, 'N');
  return $val === 'Y' ? 'Y' : 'N';
}

//function to update the time spent on a project, $id is the project id
function UpdateTimeOnProject($id) {
  global $conn;

  if ($id == "") {
    return "0";
  }
  
  //get the sum of all entries for a project
  $sql = "SELECT SUM(`minutes`) as Sum
  FROM entries 
  WHERE project_id = " . $id;
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  while ($row = mysqli_fetch_array($result)) {
    $minutes = $row['Sum'];
  }

  if ($minutes == null) {
    $minutes = 0;
  }
  //update the project with the new time
  $sql = "UPDATE projects SET `minutes` = '" . $minutes . "' WHERE id = " . $id;
  $update = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");

  return "$minutes";
}


//function to get a all the check boxes in a list
function countCheckboxes($htmlContent) {
  $checkedCount = 0;
  $uncheckedCount = 0;
  $total = 0;

  // Load the HTML content into a DOMDocument
  $dom = new DOMDocument();
  @$dom->loadHTML($htmlContent);

  // Get all li elements
  $items = $dom->getElementsByTagName('li');

  // Loop through each li element and count based on data-list attribute
  foreach ($items as $item) {
    $dataList = $item->getAttribute('data-list');
    if ($dataList === 'checked') {
      $checkedCount++;
      $total++;
    } else if ($dataList === 'unchecked') {
      $uncheckedCount++;
      $total++;
    }
  }

  return [
    'checked' => $checkedCount,
    'unchecked' => $uncheckedCount,
    'total' => $total
  ];
}


function convertCheckboxToDB($value)  {
  if ($value == "checked") {
    return "Y";
  } elseif ($value == "unchecked") {
    return "N";
  } else {
    logAction("Error in convertDBToCheckbox, value is " . $value);
    return "ERROR";
  }
}

function convertDBToCheckbox($value)  {
  if ($value == "Y") {
    return "checked=checked";
  } elseif ($value == "N") {
    return "";
  } else {
    logAction("Error in convertDBToCheckbox, value is " . $value);
    return "ERROR";
  }
}



function getProjectEntries($id) {
  global $conn;

  if ($id == "") {
    $entries["count"] = "";
    $entries["table"] = "";
    $entries["time"] = ""; 
    return $entries;
  }

  
  //get the number of entries for a project
  $sql = "SELECT COUNT(`id`) as Count
  FROM entries 
  WHERE project_id = " . $id;
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  while ($row = mysqli_fetch_array($result)) {
    $entries = ["count" => $row['Count']];
  }

  //get the total time for a project
  //This will update the DB entry for the project and return the time
  $entries["time"] = UpdateTimeOnProject($id);

  //get each entry and add it to a table
  $table = "<table><tr onclick=tableToCSV(this)>
  <th>Category</th>
  <th>Start Time</th>
  <th>End Time</th>
  <th>Time Taken</th>
  <th>Interrupted</th>
  <th>Comments</th>
  <th>Tags</th>
  </tr>";

  $sql = "SELECT entries.id, categories_id, display_name, start_time, end_time, entries.minutes, `interrupted`, `comment`, `tags`
  FROM entries
  LEFT JOIN categories
  ON entries.categories_id = categories.id
  WHERE project_id = " . $id;
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  while ($row = mysqli_fetch_array($result)) {
    $table .= "<tr onclick='newWindow(`entries.php?id=" . $row['id'] . "`)' >
    <td>" . $row['display_name'] . "</td>
    <td>" . displayTime($row['start_time'], setting('date_view')) . "</td>
    <td>" . displayTime($row['end_time'], setting('date_view')) . "</td>
    <td>" . minutesToHours($row['minutes']) . "</td>
    <td>" . $row['interrupted'] . "</td>
    <td>" . $row['comment'] . "</td>
    <td>" . dislpayTags($row['tags']) . "</td>
    </tr>";
  }
  $table .= "</table>";

  $entries["table"] = $table;

  return $entries;
}

/**
 * Project edit form + linked entries (full page and Split pane).
 *
 * @param mixed $id
 * @param array $defaults Optional title / category / description for a new project
 */
function projectEditorMarkup($id = '', $defaults = [])
{
  global $conn;

  $id = ($id === null) ? '' : (string) $id;
  $project_cat = $defaults['project_cat'] ?? '';
  $title = $defaults['title'] ?? '';
  $project_desc = $defaults['project_desc'] ?? '';
  $date_created = date('Y-m-d H:i:s');
  $date_closed = '';
  $steps = 0;
  $steps_complete = 0;
  $steps_incomplete = 0;

  if ($id !== '' && ctype_digit($id)) {
    $sql = "SELECT `id`, `project_cat`, `title`, `date_created`, `date_closed`, `project_desc`, `minutes`, `steps`, `steps_complete`, `steps_incomplete`
      FROM projects
      WHERE id = " . (int) $id;
    $result = $conn->query($sql);
    logAction("Ran SQL on DB, " . $sql, "file");
    $row = ($result) ? mysqli_fetch_array($result) : null;
    if ($row) {
      $title = $row['title'];
      $project_cat = $row['project_cat'];
      $date_created = $row['date_created'];
      $date_closed = $row['date_closed'];
      $project_desc = $row['project_desc'];
      $steps = $row['steps'];
      $steps_complete = $row['steps_complete'];
      $steps_incomplete = $row['steps_incomplete'];
      $id = (string) (int) $row['id'];
    } else {
      $id = '';
    }
  } else {
    $id = '';
  }

  $date_created_html = $date_created ? htmlspecialchars((string) displayTime($date_created, 'html'), ENT_QUOTES, 'UTF-8') : '';
  $date_closed_html = ($date_closed && $date_closed !== 'NULL')
    ? htmlspecialchars((string) displayTime($date_closed, 'html'), ENT_QUOTES, 'UTF-8')
    : '';
  $entries = getProjectEntries($id);
  $titleEsc = htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8');
  $idEsc = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
  $steps = (int) $steps;
  $steps_complete = (int) $steps_complete;
  $steps_incomplete = (int) $steps_incomplete;
  $timeLabel = minutesToHours($entries['time']);
  $countLabel = $entries['count'];

  $html = "<div class='project-editor'>";
  $html .= "<form method='post'>";
  $html .= "<input type='hidden' name='id' id='id' value='{$idEsc}'>";
  $html .= "<label for='title'>Title:</label> ";
  $html .= "<input type='text' id='title' name='title' value='{$titleEsc}'> ";
  $html .= "<span id='idDisp'>ID: {$idEsc}</span><br><br>";
  $html .= "<label for='date_created'>Date Created:</label> ";
  $html .= "<input type='datetime-local' id='date_created' name='date_created' value='{$date_created_html}'><br><br>";
  $html .= "<label for='date_closed'>Date Closed:</label> ";
  $html .= "<input type='datetime-local' id='date_closed' name='date_closed' value='{$date_closed_html}'> ";
  $html .= "<input type='button' value='Set To Current Time' onclick=\"setInputToCurrentDate('date_closed')\"><br><br>";
  $html .= "<label for='project_cat'>Project Category</label> ";
  $html .= CategoryDropList('projects', 'N', $project_cat);
  $html .= "<br><br>";
  $html .= "<p>{$steps} Steps in Project</p>";
  $html .= "<p>{$steps_complete} Complete</p>";
  $html .= "<p>{$steps_incomplete} Left to Complete</p>";
  $html .= "<div id='editor' class='quill-full-editor'>" . $project_desc . "</div>";
  $html .= "<br><input type='button' value='Save Project' onclick='saveProject()'>";
  $html .= "</form>";
  $html .= "<div class='project-editor-entries'>";
  $html .= "<p>Entries of time Spent on this project</p>";
  $html .= "<p>Time spent on Project: {$timeLabel}</p>";
  $html .= "<p>Number of entires: {$countLabel} </p>";
  $html .= $entries['table'];
  $html .= "</div></div>";

  return $html;
}


function setting($setting) {
  return $_SESSION['settings'][$setting]['value'];
}

/**
 * Read a setting value with a fallback when the key is missing.
 */
function settingValue($setting, $default = '')
{
  if (isset($_SESSION['settings'][$setting]['value'])) {
    return $_SESSION['settings'][$setting]['value'];
  }
  return $default;
}

/**
 * Build a CSS custom-property block for a light or dark palette.
 */
function themePaletteCss($prefix = '')
{
  $map = [
    'primary_background' => '--primary-background',
    'secondary_background' => '--secondary-background',
    'active_background' => '--active-background',
    'neutral_white' => '--neutral-white',
    'neutral_gray' => '--neutral-gray',
    'neutral_active' => '--neutral-active',
    'page_background' => '--page-background',
    'text_color' => '--text-color',
    'border_color' => '--border-color',
    'on_primary' => '--on-primary',
  ];

  $defaults = [
    '' => [
      'primary_background' => '#7e471b',
      'secondary_background' => '#bd760d',
      'active_background' => '#f0ec2b',
      'neutral_white' => '#ffffff',
      'neutral_gray' => '#999999',
      'neutral_active' => '#f0ec2b',
      'page_background' => '#faf8f5',
      'text_color' => '#2c2416',
      'border_color' => '#d0c4b4',
      'on_primary' => '#ffffff',
    ],
    'dark_' => [
      'primary_background' => '#a65f28',
      'secondary_background' => '#c9842e',
      'active_background' => '#c9a227',
      'neutral_white' => '#2a2420',
      'neutral_gray' => '#8a8078',
      'neutral_active' => '#5a4a28',
      'page_background' => '#14110e',
      'text_color' => '#ede6dc',
      'border_color' => '#3d342c',
      'on_primary' => '#fff8f0',
    ],
  ];

  $css = '';
  foreach ($map as $key => $var) {
    $settingKey = $prefix . $key;
    $fallback = $defaults[$prefix][$key] ?? '';
    $css .= '    ' . $var . ': ' . settingValue($settingKey, $fallback) . ";\n";
  }
  return $css;
}

//function to get the themes and add them to the HTML header
function showTheme() {
  $header_img = settingValue('header_img', '../images/Bar.jpg');
  $theme_mode = settingValue('theme_mode', 'system');
  if (!in_array($theme_mode, ['light', 'dark', 'system'], true)) {
    $theme_mode = 'system';
  }

  $lightCss = themePaletteCss('');
  $darkCss = themePaletteCss('dark_');

  echo "<style>
  :root, [data-theme=\"light\"] {
" . $lightCss . "  }

  [data-theme=\"dark\"] {
" . $darkCss . "  }

  @media (prefers-color-scheme: dark) {
    :root:not([data-theme=\"light\"]) {
" . $darkCss . "    }
  }

  #header {
    background-image: url(" . htmlspecialchars($header_img, ENT_QUOTES, 'UTF-8') . ");
  }
  </style>";

  if ($theme_mode === 'light' || $theme_mode === 'dark') {
    echo "<script>document.documentElement.setAttribute('data-theme', " . json_encode($theme_mode) . ");</script>";
  } else {
    echo "<script>document.documentElement.removeAttribute('data-theme');</script>";
  }
}



//function to check if the settings are in the session
function check_settings() {
  
  if (!isset($_SESSION['settings']['name'])) {
    logAction("Settings not found in session, redirecting to index.php to load them", "file");
    header("Location: ../index.php");
    exit();
  }

  // Reload when new preference keys appear after a migration.
  if (!isset($_SESSION['settings']['theme_mode'])
    || !isset($_SESSION['settings']['home_view'])
    || !isset($_SESSION['settings']['log_all'])
    || !isset($_SESSION['settings']['split_percent'])) {
    $_SESSION['settings'] = getSettings();
  }
}



function getSettings() {
  global $conn;
  $settings = [];
  $sql = "SELECT `id`, `setting`, `value`, `description` FROM settings";
  $result = $conn->query($sql);
//  logAction("Ran SQL on DB, " . $sql, "file");
  while ($row = mysqli_fetch_array($result)) {
    $settings[$row['setting']] = [
      "value" => $row['value'],
      "description" => $row['description']
    ];
  }

  return $settings;
}

require_once __DIR__ . '/home_views.php';


<?php
//Home Page

// V1.1 Created 2024-01-06 By MM - First version
// V1.2 Home views: compact / standard / calendar



//get the environment settings and functions
include "../php/functions.php" ;
session_start();

check_settings();

$time_sql = date('Y-m-d H:i:s', time());
$date_sql = date('Y-m-d', time());

$start_date = displayTime($date_sql, "sql");

$end_date = $date_sql . " 23:59:59";
$end_date = displayTime($end_date, "sql");

$home_view = normalizeHomeView(settingValue('home_view', 'standard'));
$calendar_span = normalizeCalendarSpan(settingValue('calendar_span', 'week'));
$calendar_day = isset($_GET['day']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['day'])
  ? $_GET['day']
  : $date_sql;
?>


<!DOCTYPE html>
<html>

<head>
  <title><?php echo $_SESSION['settings']['name']['value']; ?></title>
  <link rel='stylesheet' href='../styles/styles.css'>
  <script src="../js/functions.js"></script>

</head>

<body class="home-view-<?php echo htmlspecialchars($home_view, ENT_QUOTES, 'UTF-8'); ?>">
  <?php
  include "../partials/nav.php";
  echo homeViewSwitcher($home_view);
  ?>

  <div id="homeViewRoot">
  <?php if ($home_view === 'compact') { ?>
    <?php echo renderCompactHome(); ?>
  <?php } elseif ($home_view === 'calendar') { ?>
    <?php echo renderCalendarHome($calendar_day, $calendar_span); ?>
  <?php } else { ?>
    <div id='startStop'>
      <?php echo startStopForm(); ?>
    </div>
    <script>
      document.querySelectorAll('#startStop form').forEach(function (form) {
        form.addEventListener('submit', function () {
          syncQuillInputs(form);
        });
      });
    </script>

    <h2>Today</h2>
    <div id='todayEntries'>
      <?php
      echo showEntries($start_date, $end_date, "all" , "desc");
      ?>
    </div>

    <h2>Last 14 Days</h2>
    <div id='yesterdayEntries'>
      <?php
      $reportStart = getOldDate('14');
      echo showEntries($reportStart, $end_date, "all", "desc");
      ?>
    </div>
  <?php } ?>
  </div>

  <script>
    (function () {
      window.__homeOpenJobStart = <?php echo json_encode(openJob()); ?>;

      function tickTimers() {
        var openJobStart = window.__homeOpenJobStart;
        var timerEl = document.querySelector("#timer");
        if (timerEl) {
          timerEl.innerHTML = minutesSince(openJobStart);
        }
        var counterSpan = document.querySelectorAll("#ongoing");
        if (counterSpan.length) {
          setOngoingEntries(counterSpan, openJobStart);
        }
      }

      tickTimers();
      setInterval(tickTimers, 1000);

      if (document.getElementById('startStop')) {
        displayTags();
        getPossibleTags();
        initQuillEditors(document.getElementById('startStop'));
      }

      if (document.getElementById('compactTracker')) {
        initCompactTrackerUi(document.getElementById('compactTracker'));
      }

      checkForOpenJob();
    })();
  </script>
</body>

</html>

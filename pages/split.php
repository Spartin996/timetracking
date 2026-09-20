<?php
// Split workspace: entries on the left, projects on the right

include "../php/functions.php";
session_start();

check_settings();

$projectId = issetget('project', '');
if ($projectId !== '' && $projectId !== null && !preg_match('/^\d+$/', (string) $projectId)) {
  $projectId = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($_SESSION['settings']['name']['value'], ENT_QUOTES, 'UTF-8'); ?> - Split</title>
  <link rel='stylesheet' href='../styles/styles.css'>
  <script src="../js/functions.js"></script>
  <script src="../js/projects.js"></script>
</head>
<body class="split-page">
  <?php include "../partials/nav.php"; ?>

  <?php echo renderSplitHome($projectId); ?>

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

      if (document.getElementById('splitProjectEditor') && document.getElementById('editor')) {
        initProjectEditor(document.getElementById('splitProjectEditor'));
      }

      checkForOpenJob();
    })();
  </script>
</body>
</html>

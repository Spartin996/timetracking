<?php
//Home Page

// V1.1 Created 2024-01-06 By MM - First version



//get the environment settings and functions
include "../php/functions.php" ;
session_start();

$time_sql = date('Y-m-d H:i:s', time());
$date_sql = date('Y-m-d', time());

$start_date = displayTime($date_sql, "sql");

$end_date = $date_sql . " 23:59:59";
$end_date = displayTime($end_date, "sql");
?>


<!DOCTYPE html>
<html>

<head>
  <title><?php echo $_SESSION['settings']['name']['value']; ?></title>
  <link rel='stylesheet' href='../styles/styles.css'>
  <script src="../js/functions.js"></script>

</head>

<body>
  <?php
  include "../partials/nav.php";
  ?>


  <div id='startStop'>
    <?php echo startStopForm(); ?>
  </div>

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

  <script>
    setInterval(function() {
      document.querySelector("#timer").innerHTML = minutesSince('<?php echo openJob() ?>')
    }, 1000);

    let counterSpan = document.querySelectorAll("#ongoing");
    
    setInterval(() => setOngoingEntries(counterSpan, '<?php echo openJob() ?>')
    , 1000);

    displayTags();

    let possibleTags = [];
    getPossibleTags();

    checkForOpenJob();

  </script>
</body>

</html>
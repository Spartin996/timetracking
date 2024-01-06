<?php
//Home Page

// V1.1 Created 2024-01-06 By MM - First version



//get the environment settings and functions
include "functions.php";

$time_sql = date('Y-m-d H:i:s', time());
$date_sql = date('Y-m-d', time());
?>


<!DOCTYPE html>
<html>
  <head>
    <title>Chocolate Log</title>
    <link rel=stylesheet href=styles/styles.css>
    <script src="functions.js"></script>
    <script>setInterval(function() { document.getElementById("timer").innerHTML = minutesSince('<?php echo openJob() ?>') }, 1000);</script>
  </head>
  <body>
    <?php
    include "nav.php";
    ?>


    <div id=startStop>
      <?php echo startStopForm(); ?>
    </div>

  <h2>Today</h2>
    <div id=todayEntries>
      <?php
        echo showEntries($date_sql, $date_sql, "all");
      ?>
    </div>

    <h2>Yesterday</h2>
    <div id=yesterdayEntries>
      <?php
        $yesterday = getYesterday();
        echo showEntries($yesterday, $yesterday, "all");
      ?>
    </div>
  </body>
</html>
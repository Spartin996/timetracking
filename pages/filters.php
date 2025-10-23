<?php
//filters page to provide filters to a report page

// V1.1 Created 2024-01-07 By MM - First version

//get the environment settings and functions
include "../php/functions.php";
include "../Database.php";
session_start();

//get the categories from the db and make check boxes
$sql = "SELECT id, display_name FROM categories ORDER BY seq ASC";
$result = $conn->query($sql);
logAction("Ran SQL on DB, " . $sql, "file");
$checkboxes = "";
$i = 0;
while ($row = mysqli_fetch_array($result)) {
  $checkboxes .= "<div><input type='checkbox' name='categories" . $i . "' id='categories' value='" . $row['id'] . "' checked><label for='" . $row['id'] . "'>" . $row['display_name'] . "</label></div>";
  $i++;
}
$checkboxes .= "<input type='hidden' name='numbercats' value='" . $i . "'>"




//add a support for a session storing the dates and filters
?>

<!DOCTYPE html>
<html>

<head>
  <title><?php echo $_SESSION['settings']['name']['value']; ?> - Entry Reports</title>
  <link rel='stylesheet' href='../styles/styles.css'>
  <script src="../js/functions.js"></script>
</head>

<body>
  <?php include "../partials/nav.php"; ?>
  <form method="GET" action="all_entries.php" class="filterEntry">
    <h3>General Report</h3>
    <div>
      <label for='start_date'>Report starting?: </label>
      <input name='start_date' type='date'>
    </div>
    <div>
      <label for='end_date'>Report ending?: </label>
      <input name='end_date' type='date'>
    </div>

    <div>
      <p>Select categories to display:</p><br>
      <div class="catCheckboxes">
        <?php echo $checkboxes ?>
      </div>
    </div>
    
    <div>
      <p>Comment Includes:</p><br>
      <div>
        <input type="text" name="comment">
      </div>
    </div>
    
    <div class='tags'>
      <div>
        <label>Add Tags: </label>
        <input type='text' name='addTags' id='addTags' onkeyup='showTags(this.value)'>
      </div>
      <div id='divTags'></div>
      <input type='hidden' name='tags' id='tags' value=''>
      <div id='displayTags'>

      </div>
    </div>


    <input type='submit' value="Generate Report">

  </form>

  <form method="GET" action="employer_report.php" class="filterEntry">
    <h3>Analytics Report</h3>
    <div>
      <label for='start_date'>Report starting?: </label>
      <input name='start_date' type='date'>
    </div>
    <div>
      <label for='end_date'>Report ending?: </label>
      <input name='end_date' type='date'>
    </div>

    <input type='submit' value="Generate Report">

  </form>
</body>
<script>  
displayTags();

let possibleTags = [];

getPossibleTags();




</script>

</html>
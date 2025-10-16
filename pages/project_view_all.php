<?php 

//File to show a list of all projects


//get all the standard functions
include "../php/functions.php";
include "../connect.ini";
session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $_SESSION['settings']['name']['value']; ?> - View Project</title>
  <link rel='stylesheet' href='../styles/styles.css'>
  <script src="../js/functions.js"></script>
  <script src="../js/projects.js"></script>

</head>

<body>
  <?php include "../partials/nav.php"; ?>

  <h2>Projects</h2>  
  <form id="searchForm" class="filterEntry">
  <input type="button" value="Get Open Projects" onclick="searchProjects('standard')">
  <br>
  <input type="button" id="advancedSearchButton" value="Advanced Search" onclick="showAdvancedSearch()">
  <br>
  <div id="advancedSearchOptions">
    <label>
      Keyword: 
      <input type="text" id="searchKey" placeholder="Search">
    </label>
    <div>
      <label>
        <input type="checkbox" name="searchTitle" id="searchTitle" checked> Search Project Title
      </label>
      
      <label>
        <input type="checkbox" name="searchContent" id="searchContent" checked> Search Project Content
      </label>
    </div>

    <div>
      <label>
        From:
        <input type="date" id="startDate" placeholder="Start Date">
      </label>

      <label>
        To:
        <input type="date" id="endDate" placeholder="End Date">
      </label>

    </div>

    <div>
      <label>
        <input type="radio" name="searchRange" value="all" checked> All Projects
      </label>
      
      <label>
        <input type="radio" name="searchRange" value="startedIn"> Started In Range
      </label>
      
      <label>
        <input type="radio" name="searchRange" value="closedIn"> Closed In Range
      </label>
    </div>

    <div>
      <label>
      <input type="checkbox" id="includeClosed" checked> Include Closed Tasks
      </label>
    </div>
    
    <input type="button" value="Search" onclick="searchProjects('advanced')">
  </div>


</form>

<div id="projectList">


</div>


</body>

<script>
  //get the open projects
  searchProjects('standard');

  initializeFloatingWindow();

  // set the possible tags
    let possibleTags = [];

</script>

</html>
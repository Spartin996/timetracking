<?php
  //this is the navigation bar for the software
  //it is included in every page

  //add the theme

  showTheme();


?>

<div id='header'>
  <h1>
    <?php echo $_SESSION['settings']['name']['value']; ?>
  </h1>
</div>
<nav>
  <a href='index.php'>Home</a>
  <a href='about.php'>About</a>
  <div class="dropdown1">
    <span>Configuration <img src="../images/down-arrow.svg" alt=""></span>
    <div class="dropdown1_content">
      <a href='all_categories.php'>View Categories</a>
      <a href='all_tags.php'>View Tags</a>
    </div>
  </div>

  <div class="dropdown2">
    <span>Projects <img src="../images/down-arrow.svg" alt=""></span>
    <div class="dropdown2_content">
      <a href='project_edit.php'>Create Project</a>
      <a href='project_view_all.php'>View Projects</a>
    </div>
  </div>
  <a href='filters.php'>View Reports</a>
  <a href='#' onclick='newWindow(`entries.php`)'>Manual Entry</a>
</nav>

<div id='trackerWindow'></div>
<div id='message'></div>
<?php


//Get the environment settings and functions
include "../php/functions.php";
include "../Database.php";
session_start();

check_settings();

$id = issetget('id');

$defaults = [];
if ($id == "") {
  $defaults = [
    'project_cat' => issetget("categories"),
    'title' => issetget("title"),
    'project_desc' => issetget("project"),
  ];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $_SESSION['settings']['name']['value']; ?> - Edit Project</title>
  <link rel='stylesheet' href='../styles/styles.css'>
  <?php echo quillAssets(); ?>
  <script src="../js/functions.js"></script>
  <script src="../js/projects.js"></script>

</head>
<body>
  <?php
  include "../partials/nav.php";
  ?>
  

<br>
<br>
<?php //todo add a expended and shrink button to the editor ?>
  <?php echo projectEditorMarkup($id, $defaults); ?>


</body>
<script>
  initProjectEditor();
  initializeFloatingWindow();
</script>
</html>

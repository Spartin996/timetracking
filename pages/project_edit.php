<?php


//Get the environment settings and functions
include "../php/functions.php";
include "../connect.ini";
session_start();

$id = issetget('id');

//Creating a new entry, so leave everything blank or get data from a GET
if ($id == "") {
  $project_cat = issetget("categories");
  $title = issetget("title");
  $project_desc = issetget("project");
  $date_created = date('Y-m-d H:i:s', time());
  $date_closed = "";
  $minutes = "0";
  $steps = 0;
  $steps_complete = 0;
  $steps_incomplete = 0;
} else {
  $sql = "SELECT `id`, `project_cat`, `title`, `date_created`, `date_closed`, `project_desc`, `minutes`, `steps`, `steps_complete`, `steps_incomplete` 
  FROM projects
  WHERE id = " . $id;
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  $row = mysqli_fetch_array($result);
  $title = $row["title"];
  $project_cat = $row["project_cat"];
  $date_created = $row["date_created"];
  $date_closed = $row["date_closed"];
  $project_desc = $row["project_desc"];
  $minutes = $row["minutes"];
  $steps = $row["steps"];
  $steps_complete = $row["steps_complete"];
  $steps_incomplete = $row["steps_incomplete"];
}

$date_created = displayTime($date_created, "html");
$date_closed = displayTime($date_closed, "html");


$entries = getProjectEntries($id);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $_SESSION['settings']['name']['value']; ?> - Edit Project</title>
  <link rel='stylesheet' href='../styles/styles.css'>
  <!-- Include stylesheet for Quill -->
  <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
  <script src="../js/functions.js"></script>
  <script src="../js/projects.js"></script>
  <!-- Include the Quill library -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

</head>
<body>
  <?php
  include "../partials/nav.php";
  ?>
  

<br>
<br>
<?php //todo add a expended and shrink button to the editor ?>
  <form method="post">
    <br>
    <input type="hidden" name="id" id="id" value="<?php echo $id ?>">

    <label for="title">Title:</label>
    <input type="text" id="title" name="title" value="<?php echo $title ?>">
    <span id="idDisp">ID: <?php echo $id ?></span><br><br>

    <label for="date_created">Date Created:</label>
    <input type="datetime-local" id="date_created" name="date_created" value="<?php echo $date_created ?>">
    <br><br>

    <label for="date_closed">Date Closed:</label>
    <input type="datetime-local" id="date_closed" name="date_closed" value="<?php echo $date_closed ?>">
    <input type="button" value="Set To Current Time" onclick="setInputToCurrentDate('date_closed')">
    <br><br>
    
    <label for="project_cat">Project Category</label>
    <?php
      echo CategoryDropList("projects", "N", $project_cat);
    ?>
    
    <br><br>
    <p><?php echo $steps; ?> Steps in Project</p>
    <p><?php echo $steps_complete; ?> Complete</p>
    <p><?php echo $steps_incomplete; ?> Left to Complete</p>

    <!-- Create the editor container -->
    <div id="editor">
      <?php echo $project_desc ?>
    </div>

    <br>
    <input type="button" value="Save Project" onclick="saveProject()">
  </form>

  <div>
    <p>Entries of time Spent on this project</p>
    <p>Time spent on Project: <?php echo minutesToHours($entries['time']); ?></p>
    <p>Number of entires: <?php echo $entries['count']; ?> </p>
      <?php echo $entries['table']; ?>

  </div>


</body>
<!-- Initialize Quill editor -->
<script>
  let toolbarOptions = [
  [{ 'list': 'check'}, { 'list': 'bullet' }, { 'list': 'ordered' }],
  ['bold', 'underline', 'strike'],// toggled buttons
  ['code-block'],
  ['link', 'image'],
  [{ 'header': 2 }],// custom button values
  [{ 'indent': '-1'}, { 'indent': '+1' }],// outdent/indent

  [{ 'color': [] }, { 'background': [] }],// dropdown with defaults from theme

  ['clean']// remove formatting button
];


  const quill = new Quill('#editor', {
    modules: {
      toolbar: toolbarOptions
    },
    theme: 'snow',
  });


  //add the entries to the window
  initializeFloatingWindow();
  // set the possible tags
    let possibleTags = [];
</script>
</html>
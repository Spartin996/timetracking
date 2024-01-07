<?php
//categories page to edit or create categories

// V1.1 Created 2024-01-07 By MM - First version

//get the environment settings and functions
include "functions.php";


$id = issetget("id");

//check if form is submitted, if so update row
if(isset($_GET['hasBeenSub'])) {
  $display_name = issetget('display_name');
  $description = issetget('description');
  $active = issetget('active', 'N');
    //set displays for the form
    if($active == "Y") {
      $yes = " checked=checked";
      $no = "";
    } else {
      $yes = "";
      $no =  " checked=checked";
    }
  $seq = issetget('seq');

  if($id != "") {
    //update sql goes here
    $sql = "UPDATE `categories` 
      SET `display_name` = '" . $display_name . "'
      , `description` = '" . $description . "'
      , `active` = '" . $active . "'
      , `seq` = '" . $seq . "'
      WHERE id = " . $id;
  } else {
    //insert goes here
    $sql = "INSERT INTO `categories` 
    (`id`, `display_name`, `description`, `active`, `seq`)
     VALUES (NULL, '" . $display_name . "', '" . $description . "', '" . $active . "', '" . $seq . "');";
  }
  $run = $conn->query($sql);

} else {
//I have not submitted the form yet
//check if I am creating a new entry, if so leave blank
  if($id == "") {
    $display_name = issetget('display_name');
    $description = issetget('description');
    $active = issetget('active', 'N');
    //set displays for the form
      if($active == "N") {
        $yes = "";
        $no =  " checked=checked";
      } else {
        $yes = " checked=checked";
        $no = "";
      }
    $seq = issetget('seq');
  } else {
  $sql = "SELECT id, display_name, `description`, active, seq
    FROM categories
    WHERE id = " . $id;
  $result = $conn->query($sql);
  $row = mysqli_fetch_array($result);
  $display_name = $row['display_name'];
  $description = $row['description'];
  $active = $row['active'];
  //set displays for the form
    if($active == "Y") {
      $yes = " checked=checked";
      $no = "";
    } else {
      $yes = "";
      $no =  " checked=checked";
    }
  $seq = $row['seq'];
  }
}


?>


<!DOCTYPE html>
<html>
  <head>
    <title>Chocolate Log - edit categories</title>
    <link rel=stylesheet href=styles/styles.css>
    <script src="functions.js"></script>
  </head>
  <body>
    <form method="GET" action="categories.php" class="manEditCats">

      <input name=hasBeenSub type="hidden" value="submitted">

      <input name=id type="hidden" value=<?php echo $id; ?>>
      <div>
        <label for=display_name>Display Name: </label>
        <input name=display_name type=text value=<?php echo $display_name; ?>>
      </div>
      <div>
        <label for=description>Description: </label>
        <input name=description type=text value=<?php echo $description; ?>>
      </div>
      <div>
        <label for=y>Active</label>
        <input type="radio" name="active" value=Y <?php echo $yes; ?>>
        <label for=y>Inactive</label>
        <input type="radio" name="active" value=N <?php echo $no; ?>>
      </div>
      <div>
        <label for=seq>Sequence: </label>
        <input name=seq type=text value=<?php echo $seq; ?>>
      </div>

      <input type=submit value=Save>
      
    </form>
  </body>
</html>
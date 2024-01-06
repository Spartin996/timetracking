# General_db_editor

This is a generic front end for mysql database.

For it to function you will need:

  You will need to have a php and mysql enviroment set up this was devolped in php 8.2 using this software as a localhost server https://www.apachefriends.org/

  connect.ini in a parent director
    you will need it to have this code in it

        // Database Connect

        $dbhost = "localhost";
        $dbuser = "root";
        $dbpass = "";
        $db = "diary";
        
        global $conn;
        $conn = new mysqli($dbhost, $dbuser, $dbpass, $db);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
          }
          
    
          // Change the line below to your timezone!
          date_default_timezone_set('Australia/Brisbane');

  You will need a Mysql db and tables to edit.
  any foreign keys need to table_id for example category_id will get the ID from the category table.


  Finally a link with links to the tables that you wish to be able to view and edit.
  for example <a href='general_db_editor/view_table.php?table=category'>Category Table</a>



# Assumbtions and Limitations

  Each table must have a feild called ID with AUTO_INCRIMENT

  Foreign keys will be INT of 4 named in the format of table_id

  Modifcation tracking will will be in a feild called last_modified type DATETIME Length NULL

  Longtext feils will append a updated datetime on the end of the feild as a heading.

  Varchar of 1 is considered a booleen checkbox supporting a Y and N value display as a Yes No Radio.


# features coming

need to add a limit to the View_table.php with multiple pages and filters.
  This might be done with a describe to find foreign keys and make filters based on those.
  find check boxs and filter by them
  find dates and allow for before and after and between filters.

Need to add a Tagging system, some sort of feild that has a set of predfined tags that can be easily searched.

maybe add a setting text file that has titles, Favicons and default functionality on some feilds, for example when editing a do you include a updated header.

need to add data santising so that the database can include example SQL code without damaging anything.

need to add file upload (stored in a directory) and linked in the db as a filepath. (may not be generic)
<?php
//create a DB class
class Database
{
  public $connection;

  public function __construct($config, $dbuser, $dbpass)
  {
    try {

      $dsn = "mysql:" . http_build_query($config, "", ";");

      $this->connection = new PDO($dsn . ";", $dbuser, $dbpass, [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
      // set the PDO error mode to exception
      $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      //echo "Connected successfully";
    } catch (PDOException $e) {
      echo "Connection failed: " . $e->getMessage();
    }
  }

  public function query($sql, $params)
  {
    //$sql is the Query
    //for example, SELECT id, display_name FROM categories WHERE id = :id ORDER BY seq ASC
    //Params is a keyed array of params for the query ['id' => 1]

    $statement = $this->connection->prepare($sql);
    $statement->execute($params);


    return $statement;
  }
}

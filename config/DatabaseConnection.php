<?php

namespace Config;

use PDO;
use PDOException;


readonly class DatabaseConnection
{


    public function __construct(
    private string $dbName,
    private string $user,
    private string $password
  ) {
  }




  public function connect() : string|object
  {


    try {
      $connect = new PDO("mysql:host=localhost;dbname=$this->dbName", "$this->user", "$this->password");
    } catch (PDOException $e) {
      return "Database Error :" . $e->getMessage();
    }

    return $connect;
  }
}

<?php

namespace Model;

use Config\DatabaseConnection;
use Enumeration\UserType;



readonly class User
{


  public function __construct(private DatabaseConnection $connector)
  {
  }


  public function createUser(object $user): ?array
  {

    $dbConnect = $this->connector->connect();

    $userData = $user->getData();
    $username = $userData["username"];
    $file = $userData["profileImage"];
    $email = $userData["email"];
    $password = $userData["password"];

    $statement = $dbConnect->prepare('SELECT username,email FROM user WHERE username = :username OR  email = :email');
    $statement->bindParam("username", $username);
    $statement->bindParam("email", $email);
    $statement->execute();
    $result = $statement->fetch();


    if (!$result) {
      $fileRequirements = explode(';', $file);
      $fileSettings["file_name"] = $fileRequirements[0];
      $fileSettings["tmp_name"] = $fileRequirements[1];
      $fileSettings["directory"] = $fileRequirements[2];
      $filePath = "http://localhost/P5_Créez votre premier blog en PHP - Dembele Mamadou/public/assets/images/" . $fileSettings["file_name"];
      $statement2 = $dbConnect->prepare("INSERT INTO user (username,profile_image,email,password,type) VALUES(?,?,?,?,?)");
      $values = [
        $username,
        $filePath,
        $email,
        $password,
        UserType::USER->value
      ];
      $statement2->execute($values);
      move_uploaded_file($fileSettings["tmp_name"], $fileSettings["directory"] . "/" . $fileSettings["file_name"]);
      header("HTTP/1.1 302");
      header("Location: ?selection=sign_in");
    }

    return !empty($result) ? $result : null;
  }

  public function loginUser(string $email, string $password): ?array
  {

    $dbConnect = $this->connector->connect();
    $statement = $dbConnect->prepare("SELECT username,email,type,password FROM user WHERE email = :email  ");
    $statement->bindParam(":email", $email);
    $statement->execute();
    $user = $statement->fetch();
    switch (true) {
      case $user && $user["type"] == UserType::USER->value:
        $checkPassword = password_verify($password, $user['password']);
        $username = $user["username"];
        $typeUser = $user["type"];
        if (!$checkPassword) {
          header('HTTP/1.1 401');
          return ["password_error" => 1];
        }
        header("HTTP/1.1 302");
        header("Location: index.php?selection=blog");
        return ["username" => $username, "type_user" => $typeUser];

      case $user && $user["type"] == UserType::ADMIN->value:
        $checkPassword = password_verify($password, $user['password']);
        $username = $user["username"];
        $typeUser = $user["type"];
        if (!$checkPassword) {
          header('HTTP/1.1 401');
          return ["password_error" => 1];
        }
        header("HTTP/1.1 302");
        header("Location: index.php?selection=blog");
        return ["username" => $username, "type_user" => $typeUser];


      default:
        header('HTTP/1.1 400');
        return ["email_error" => 1];
    }
  }

  public function insertSessionData(array $sessionData): ?array
  {

    $dbConnect = $this->connector->connect();

    $statement = $dbConnect->prepare("SELECT username FROM session WHERE username = :username");
    $statement->bindParam("username", $sessionData["username"]);
    $statement->execute();
    $result = $statement->fetch();
    
    if (!$result) {
     
      $idSession =  str_replace("/", "", base64_encode(random_bytes(50)));
      $sessionData["id_session"] = $idSession;
      $insertData = $dbConnect->prepare("INSERT INTO session (id_session,username,user_type) VALUES(?,?,?)");
  
      $values = [$sessionData["id_session"], $sessionData["username"], $sessionData["type_user"]];
      $insertData->execute($values);
    }
    
    return null;
  
  }


  public function getIdSessionData($sessionData): ?array
  {
    $dbConnect = $this->connector->connect();
    $statement = $dbConnect->prepare("SELECT username,type FROM user WHERE username = :username AND type = :type_user");

    $statement->bindParam("username", $sessionData["username"]);
    $statement->bindParam("type_user", $sessionData["type_user"]);
    $statement->execute();
    $result = $statement->fetch();

    if ($result) {
      $statementSession = $dbConnect->prepare("SELECT id_session,username,user_type FROM session WHERE username = :username AND user_type = :type_user");
      $statementSession->bindParam("username", $sessionData["username"]);
      $statementSession->bindParam("type_user", $sessionData["type_user"]);

      $statementSession->execute();
      $resultStatementSession = $statementSession->fetch();
      $idSession = !empty($resultStatementSession) ?  $resultStatementSession["id_session"] : null;
      if(!empty($idSession)) return ["session_id" => $idSession];
    }
    return  null;

  }

  public function logout(array $sessionData) :?array
  {
    $dbConnect = $this->connector->connect();
    $statementSession = $dbConnect->prepare("SELECT id,id_session,username,user_type FROM session WHERE id_session = :id_session AND username = :username AND user_type = :type_user");
    $statementSession->bindParam("username", $sessionData["username"]);
    $statementSession->bindParam("type_user", $sessionData["type_user"]);
    $statementSession->bindParam("id_session", $sessionData["session_id"]);

    $statementSession->execute();
    
    $result = $statementSession->fetch();
    
    if($result){
      $idSessionInDb = $result["id"];

      $deleteSession = $dbConnect->prepare("DELETE FROM session WHERE id = :id");
      $deleteSession->bindParam("id",$idSessionInDb);
      $deleteSession->execute();
      header("HTTP/1.1 302");
      header("Location: index.php?selection=blog");
      return ["logout" => 1];
    }
    return null;
  }
}

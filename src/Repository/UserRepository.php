<?php

namespace Repository;

use BackedEnum;
use Config\DatabaseConnection;
use Enumeration\UserType;
use Model\UserModel;



class UserRepository
{


  public function __construct(private readonly DatabaseConnection $connector)
  {
  }


  public function createUser(string $username, string $file, string $email, string $password, BackedEnum $userType): ?UserModel
  {

    $dbConnect = $this->connector->connect();
    $userModel = new UserModel($username, $file, $email, $password, $userType, null, null);


    $statement = $dbConnect->prepare('SELECT username,email FROM user WHERE username = :username OR  email = :email');
    $statement->bindParam("username", $username);
    $statement->bindParam("email", $email);
    $statement->execute();
    $result = $statement->fetch();

    switch (true) {
      case !$result:
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
          $userType->value
        ];
        $statement2->execute($values);
        move_uploaded_file($fileSettings["tmp_name"], $fileSettings["directory"] . "/" . $fileSettings["file_name"]);
        return null;

      case $result["username"] == $userModel->getUsername():
        $userModel->setUsernameAvailability(false);
        return $userModel;

      case $result["email"] == $userModel->getEmail():
        $userModel->setEmailAvailability(false);
        return $userModel;
    }
  }

  public function loginUser(string $email, string $password): ?array
  {

    $dbConnect = $this->connector->connect();
    $statement = $dbConnect->prepare("SELECT id,username,email,type,password FROM user WHERE email = :email  ");
    $statement->bindParam(":email", $email);
    $statement->execute();
    $user = $statement->fetch();

    if ($user && $user["type"] == UserType::USER->value || $user && $user["type"] == UserType::ADMIN->value) {
      $checkPassword = password_verify($password, $user['password']);
      $username = $user["username"];
      $typeUser = $user["type"];
      $userId = $user["id"];
      if (!$checkPassword) {
        return ["password_error" => 1];
      }

      return ["username" => $username, "type_user" => $typeUser, "id_user" => $userId];
    } else {
      return ["email_error" => 1];
    }
  }






  public function logout(array $sessionData): ?array
  {
    $dbConnect = $this->connector->connect();
    $statementSession = $dbConnect->prepare("SELECT id,id_session,username,user_type FROM session WHERE id_session = :id_session AND username = :username AND user_type = :type_user");
    $statementSession->bindParam("username", $sessionData["username"]);
    $statementSession->bindParam("type_user", $sessionData["type_user"]);
    $statementSession->bindParam("id_session", $sessionData["session_id"]);

    $statementSession->execute();

    $result = $statementSession->fetch();

    if ($result) {
      $idSessionInDb = $result["id"];

      $deleteSession = $dbConnect->prepare("DELETE FROM session WHERE id = :id");
      $deleteSession->bindParam("id", $idSessionInDb);
      $deleteSession->execute();
    }
    return ["logout" => 1];
  }

  public function getAllUserNotifications(array $sessionData): ?array
  {
    $dbConnect = $this->connector->connect();



    $statementGetAllNotifications = $dbConnect->prepare("SELECT * FROM user_notification WHERE idUser = :idUserOfSession");

    $statementGetAllNotifications->bindParam("idUserOfSession", $sessionData["id_user"]);
    $statementGetAllNotifications->execute();

    return $statementGetAllNotifications->fetchAll();
  }

  public function deleteNotification(int $idNotification): ?array
  {
    $dbConnect = $this->connector->connect();
    $statementDeleteNotification = $dbConnect->prepare("DELETE FROM user_notification WHERE id = :idNotification");
    $statementDeleteNotification->bindParam("idNotification", $idNotification);
    $statementDeleteNotification->execute();
    return ["notification_delete" => 1];
  }
}

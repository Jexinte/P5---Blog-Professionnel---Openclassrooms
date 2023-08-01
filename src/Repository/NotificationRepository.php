<?php

namespace Repository;

use Config\DatabaseConnection;

class NotificationRepository
{

  public function __construct(private DatabaseConnection $connector)
  {
  }

  public function createNotification(object $notificationModel): null
  {
    $dbConnect = $this->connector->connect();
    
    $statementToCreateNotification = $dbConnect->prepare("INSERT INTO user_notification (idArticle,idUser,approved,rejected,feedback_administrator) VALUES(?,?,?,?,?)");
    $values = [
      $notificationModel->getiIdArticle(),
      $notificationModel->getiIdUser(),
      $notificationModel->getApproved(),
      $notificationModel->getRejected(),
      $notificationModel->getFeedbackAdministrator()
    ];
    $statementToCreateNotification->execute($values);
    return null;
  }

  public function getAllUserNotifications(array $sessionData): ?array
  {
    $dbConnect = $this->connector->connect();



    $statementGetAllNotifications = $dbConnect->prepare("SELECT * FROM user_notification WHERE idUser = :idUserOfSession");

    $statementGetAllNotifications->bindParam("idUserOfSession", $sessionData["id_user"]);
    $statementGetAllNotifications->execute();

    return $statementGetAllNotifications->fetchAll();
  }

  public function deleteNotification(int $idNotification): null
  {
    $dbConnect = $this->connector->connect();
    $statementDeleteNotification = $dbConnect->prepare("DELETE FROM user_notification WHERE id = :idNotification");
    $statementDeleteNotification->bindParam("idNotification", $idNotification);
    $statementDeleteNotification->execute();
    return null;
  }
}

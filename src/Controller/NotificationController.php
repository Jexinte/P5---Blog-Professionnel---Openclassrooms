<?php

namespace Controller;

use Model\NotificationModel;
use Repository\NotificationRepository;

class NotificationController
{

  public function __construct(private readonly NotificationRepository $notificationRepository)
  {
  }

  public function handleCreateNotification(array $validation): void
  {
    
    switch (true) {
      case array_key_exists("approved", $validation):
        $notificationModel = new NotificationModel($validation["id_article"], $validation["id_user"], $validation["approved"], null, $validation["feedback"]);

        $this->notificationRepository->createNotification($notificationModel);
        break;
      case array_key_exists("rejected", $validation):
        $notificationModel = new NotificationModel($validation["id_article"], $validation["id_user"], null, $validation["rejected"], $validation["feedback"]);
      
        $this->notificationRepository->createNotification($notificationModel);
        break;
    }
  }

  public function handleGetAllUserNotifications(array $validation): ?array
  {
    $notificationRepository = $this->notificationRepository;
    return $notificationRepository->getAllUserNotifications($validation);
  }

  public function handleDeleteNotification(int $idNotification): null
  {
    $notificationRepository = $this->notificationRepository;
    return $notificationRepository->deleteNotification($idNotification);
  }
}

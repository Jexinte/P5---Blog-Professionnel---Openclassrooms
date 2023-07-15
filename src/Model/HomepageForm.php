<?php

namespace Model;

use Config\DatabaseConnection;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;




readonly class HomepageForm
{

  public function __construct(private DatabaseConnection $connector)
  {
  }

  public function insertDataInDatabase(object $form_data): array
  {

    $dbConnect = $this->connector->connect();
    $data = $form_data->getData();
    $firstname = $data["firstname"];
    $lastname = $data["lastname"];
    $email = $data["email"];
    $subject = $data["subject"];
    $message = $data["message"];

    $statement = $dbConnect->prepare("INSERT INTO form_message(idUser,firstname,lastname,email,subject,message) VALUES(?,?,?,?,?,?)");
    $values = [
      null,
      $firstname,
      $lastname,
      $email,
      $subject,
      $message
    ];
    $statement->execute($values);
    header("HTTP/1.1 201");
    return ["data_saved" => 1];
  }

  public function getDataFromDatabase(array $arr): ?array
  {

    $dbConnect = $this->connector->connect();
    $result =  array_key_exists("data_saved", $arr) && in_array(1, $arr) ? $arr : false;

    if (is_array($result)) {
      $statement = $dbConnect->prepare("SELECT * FROM form_message ORDER BY id DESC LIMIT 1");
      $statement->execute();
      $resReq = $statement->fetch();
      header("HTTP/1.1 200");
      return [
        "data_retrieved" => 1,
        "user" =>  $resReq
      ];
    }
  }


  public function sendMailAdmin(array $data): ?array
  {


    $key = file_get_contents("../config/stmp_credentials.json");
    $key_2 = file_get_contents("../config/stmp_credentials.json");
    $username = json_decode($key, true);
    $password = json_decode($key_2, true);

    $mail = new PHPMailer(true);
    $result = !empty($data);

    if (!$result) {
      header("HTTP/1.1 500");
      return  ["message_sent_failed" => "Votre message n'a pu être envoyé , veuillez réessayez plus tard !"];
    } else {
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true;
      $mail->Username = $username["username"]; // Name of the owner application password
      $mail->Password = $password["password"]; // Gmail Password Application
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = 587;
      $mail->SMTPOptions = array(
        'ssl' => array(
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
        )
      );

      $mail->setFrom($username["username"], 'Message du formulaire de contact');
      $mail->addAddress($username["username"]);
      $mail->addReplyTo($data["user"]["email"], $data["user"]["firstname"]);
      $mail->isHTML();

      $mail->Subject = $data["user"]["subject"];
      $mail->Body = "Le message suivant a été envoyé par <strong>" . $data["user"]["firstname"] . " " . $data["user"]["lastname"] . "</strong> via le formulaire de contact  : <br><br><br>" . $data["user"]["message"];

      $mail->send();
      header("HTTP/1.1 200");
      return ["message_sent" => "Votre message a bien été envoyé !"];
    }
  }
}

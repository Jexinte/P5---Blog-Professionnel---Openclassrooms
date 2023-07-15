<?php

namespace Controller;

use Enumeration\UserType;

use Exceptions\EmailErrorEmptyException;
use Exceptions\EmailWrongFormatException;
use Exceptions\PasswordErrorEmptyException;
use Exceptions\PasswordWrongFormatException;
use Exceptions\UsernameWrongFormatException;
use Exceptions\UsernameErrorEmptyException;
use Exceptions\FileTypeException;
use Exceptions\FileErrorEmptyException;

use Model\User;
use Model\UserModel;






readonly class UserController
{


  public function __construct(private User $user)
  {
  }


  public function handleUsernameField(string $username): array|string
  {

    $userRegex =  "/^[A-Z][A-Za-z\d]{2,10}$/";
    switch (true) {
      case empty($username):
        header("HTTP/1.1 400");
        throw new UsernameErrorEmptyException();
      case !preg_match($userRegex, $username):
        header("HTTP/1.1 400");
        throw new UsernameWrongFormatException();
      default:
        return ["username" => $username];
    }
  }
  public function handleFileField(array $file): array|string
  {
    switch (true) {
      case !empty($file["name"]) && $file["error"] == UPLOAD_ERR_OK:
        $filename = $file["name"];
        $dirImages = "../public/assets/images/";
        $filenameTmp = $file['tmp_name'];
        $extensionOfTheUploaded_file = explode('.', $filename);
        $authorizedExtensions = array("jpg", "jpeg", "png", "webp");

        if (in_array($extensionOfTheUploaded_file[1], $authorizedExtensions)) {
          $bytesToStr = str_replace("/", "", base64_encode(random_bytes(9)));
          $filenameAndExtension = explode('.', $filename);
          $filenameGenerated = $bytesToStr . "." . $filenameAndExtension[1];

          return ["file" => "$filenameGenerated;$filenameTmp;$dirImages"];
        } else {
          header("HTTP/1.1 400");
          throw new FileTypeException();
        }

      default:
        header("HTTP/1.1 400");
        throw new FileErrorEmptyException(FileErrorEmptyException::FILE_MESSAGE_ERROR_NO_FILE_SELECTED);
    }
  }


  public function handleEmailField(string $email): array|string
  {

    $emailRegex = "/^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$/";

    switch (true) {
      case empty($email):
        header("HTTP/1.1 400");
        throw new EmailErrorEmptyException();
      case !preg_match($emailRegex, $email):
        header("HTTP/1.1 400");
        throw new EmailWrongFormatException();
      default:
        return ["email" => $email];
    }
  }
  public function handlePasswordField(string $password): array|string
  {

    $passwordRegex = "/^(?=.*[A-Z])(?=.*\d).{8,}$/";
    switch (true) {
      case empty($password):
        header("HTTP/1.1 400");
        throw new PasswordErrorEmptyException();
      case !preg_match($passwordRegex, $password):
        header("HTTP/1.1 400");
        throw new PasswordWrongFormatException();
      default:
        $hashPassword = password_hash($password, PASSWORD_DEFAULT);
        return ["password" => $hashPassword];
    }
  }


  public function signUpValidator(string $username, array $file, string $email, string $password): array|string
  {

    $usernameResult = $this->handleUsernameField($username);

    $emailResult = $this->handleEmailField($email);
    $passwordResult = $this->handlePasswordField($password);
    $fileResult = $this->handleFileField($file);
    $counter = 0;

    $fields = [
      "username" => $usernameResult,
      "email" => $emailResult,
      "password" => $passwordResult,
      "file" => $fileResult
    ];



    foreach ($fields as $v) {
      if (is_array($v)) $counter++;
    }
    if ($counter == 4) {
      $userRepository = $this->user;
      $username = $fields["username"];
      $fileSettings = $fields["file"];
      $email = $fields["email"];
      $password = $fields["password"];
      $userType = UserType::USER;
      $userData = new UserModel($username["username"], $fileSettings["file"], $email["email"], $password["password"], $userType);

      $userDb = $userRepository->createUser($userData);

      switch (true) {
        case  $userDb["username"] === $username["username"] && $userDb["email"] === $email["email"]:
          header("HTTP/1.1 400");
          return ["username_unavailable" => "Le nom d'utilisateur " . $username["username"] . " n'est pas disponible !", "email_unavailable" => "L'adresse email " . $email["email"] . " n'est pas disponible !"];

        case $userDb["username"] === $username["username"]:
          header("HTTP/1.1 400");
          return ["username_unavailable" => "Le nom d'utilisateur " . $username["username"] . " n'est pas disponible !"];

        case $userDb["email"] === $email["email"]:
          header("HTTP/1.1 400");
          return ["email_unavailable" => "L'adresse email " . $email["email"] . " n'est pas disponible !"];
      }
    }
    return null;
  }


  public function verifyEmailOnLogin(string $email): array|string
  {

    $emailRegex = "/^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$/";
    switch (true) {
      case empty($email):
        header("HTTP/1.1 400");
        throw new EmailErrorEmptyException();
      case !preg_match($emailRegex, $email):
        header("HTTP/1.1 400");
        throw new EmailWrongFormatException();
      default:
        return ["email" => $email];
    }
  }

  public function verifyPasswordOnLogin(string $password): array|string
  {


    if (empty($password)) {
      header("HTTP/1.1 400");
      throw new PasswordErrorEmptyException(PasswordErrorEmptyException::PASSWORD_MESSAGE_ERROR_EMPTY);
    }
    return ["password" => $password];
  }


  public function loginValidator(string $email, string $password): ?array
  {
    $userRepository = $this->user;
    $emailResult = $this->verifyEmailOnLogin($email);
    $passwordResult = $this->verifyPasswordOnLogin($password);
    $counter = 0;

    $fields = [
      "email" => $emailResult,
      "password" => $passwordResult
    ];

    $emailField = $fields["email"]["email"];
    $passwordField = $fields["password"]["password"];
    foreach ($fields as $v) {
      if (is_array($v)) $counter++;
    }

    if ($counter == 2) {

      $login = $userRepository->loginUser($emailField, $passwordField);
      return match (true) {
        array_key_exists("password_error", $login) => ["password_error" => "Le mot de passe est incorrect !"],
        array_key_exists('email_error', $login) => ["email_error" => "Oups ! Nous n'avons trouvé aucun compte associé à cette adresse e-mail. Assurez-vous que vous avez saisi correctement votre adresse e-mail et réessayez"],
        default => $login
      };
    }
  }

  public function handleInsertSessionData(array $arr): void
  {
    $userRepository = $this->user;

    $userRepository->insertSessionData($arr);
  }

  public function handleGetIdSessionData($arr): ?array
  {
    $userRepository = $this->user;

    return $userRepository->getIdSessionData($arr);
  }

  public function handleLogout(array $sessionData): ?array
  {
    $userRepository = $this->user;
    return $userRepository->logout($sessionData);
  }
}

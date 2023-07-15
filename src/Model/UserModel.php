<?php

namespace Model;


use Enumeration\UserType;

class UserModel
{

  public function __construct(
    protected string $username,
    protected string $profileImage,
    protected string $email,
    protected string $password,
    protected UserType $type,
  ) {
  }

  public function getData(): array
  {
    return ["username" => $this->username, "profileImage" => $this->profileImage, "email" => $this->email, "password" => $this->password];
  }
}

<?php

namespace Exceptions;

use Exception;

class UsernameWrongFormatException extends Exception
{
  const USERNAME_MESSAGE_ERROR_WRONG_FORMAT = "Oops ! Merci de suivre le format ci-dessous pour votre nom d'utilisateur !";
}

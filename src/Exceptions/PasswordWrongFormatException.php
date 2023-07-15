<?php

namespace Exceptions;

use Exception;

class PasswordWrongFormatException extends Exception
{
  const PASSWORD_MESSAGE_ERROR_WRONG_FORMAT = "Oops! Le format de votre mot de passe est incorrect, merci de suivre le format ci-dessous :";
}

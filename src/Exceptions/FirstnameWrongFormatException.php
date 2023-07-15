<?php

namespace Exceptions;

use Exception;


class FirstnameWrongFormatException extends Exception
{
  const FIRSTNAME_MESSAGE_ERROR_WRONG_FORMAT = "La première lettre de votre prénom doit être en majuscule et ne doit pas contenir de caractères spéciaux tels que les éléments suivant : ?,!, etc... ";
}

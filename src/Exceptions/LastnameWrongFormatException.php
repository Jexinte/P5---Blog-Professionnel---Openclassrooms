<?php

namespace Exceptions;

use Exception;

class LastnameWrongFormatException extends Exception
{
  const LASTNAME_MESSAGE_ERROR_WRONG_FORMAT = "La première lettre de votre nom doit être en majuscule et ne doit pas contenir de caractères spéciaux tels que les éléments suivant : ?,!, etc... ";
}

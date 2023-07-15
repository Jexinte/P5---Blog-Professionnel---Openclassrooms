<?php

namespace Exceptions;

use Exception;



class EmailWrongFormatException extends Exception
{
  const EMAIL_MESSAGE_ERROR_WRONG_FORMAT = "Oops! Le format de votre saisie est incorrect, merci de suivre le format requis : nomadressemail@domaine.extension";
}

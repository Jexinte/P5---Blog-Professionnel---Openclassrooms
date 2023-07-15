<?php

namespace Exceptions;

use Exception;

class SubjectWrongFormatException extends Exception
{
  const SUBJECT_MESSAGE_ERROR_MIN_20_CHARS_MAX_100_CHARS = "Le texte doit contenir minimum 20 caractères et ne peut en excéder 100 !";
}

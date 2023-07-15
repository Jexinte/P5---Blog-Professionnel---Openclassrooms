<?php

namespace Exceptions;

use Exception;

class ContentMessageWrongFormatException extends Exception
{
  const CONTENT_MESSAGE_ERROR_MIN_20_CHARS_MAX_500_CHARS = "Le texte doit contenir minimum 20 caractères et ne peut en excéder 500 !";
}

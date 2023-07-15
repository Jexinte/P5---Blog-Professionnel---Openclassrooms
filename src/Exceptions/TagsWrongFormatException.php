<?php

namespace Exceptions;

use Exception;

class TagsWrongFormatException extends Exception
{
  const TAGS_MESSAGE_ERROR_MAX_3_TAGS = "Le nombres de tags maximum de tag autorisé dans un article est limité à 3. Chaque nouveau tag doit commencer par une majuscule !";
}

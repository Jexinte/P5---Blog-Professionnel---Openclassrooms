<?php

namespace Exceptions;

use Exception;

class TagsErrorEmptyException extends Exception
{
  const TAGS_ERROR_EMPTY = "Ce champ ne peut être vide !";
}

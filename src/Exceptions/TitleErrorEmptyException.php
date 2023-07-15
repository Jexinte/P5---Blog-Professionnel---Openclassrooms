<?php

namespace Exceptions;

use Exception;

class TitleErrorEmptyException extends Exception
{
  const TITLE_MESSAGE_ERROR_EMPTY = "Ce champ ne peut être vide !";
}

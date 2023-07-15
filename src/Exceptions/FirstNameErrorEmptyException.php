<?php

namespace Exceptions;

use Exception;

class FirstNameErrorEmptyException extends Exception
{
  const FIRSTNAME_MESSAGE_ERROR_EMPTY = "Ce champ ne peut être vide !";
}

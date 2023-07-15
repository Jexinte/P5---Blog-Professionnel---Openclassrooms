<?php

namespace Exceptions;

use Exception;

class PasswordErrorEmptyException extends Exception
{
  const PASSWORD_MESSAGE_ERROR_EMPTY = "Ce champ ne peut être vide !";
}

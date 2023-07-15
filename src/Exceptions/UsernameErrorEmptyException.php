<?php

namespace Exceptions;

use Exception;

class UsernameErrorEmptyException extends Exception
{
  const USERNAME_MESSAGE_ERROR_EMPTY = "Ce champ ne peut-être vide !";
}

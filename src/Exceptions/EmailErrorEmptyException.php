<?php

namespace Exceptions;

use Exception;

class EmailErrorEmptyException extends Exception
{
  const EMAIL_MESSAGE_ERROR_EMPTY = "Ce champ ne peut-être vide !";
}

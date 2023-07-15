<?php

namespace Exceptions;

use Exception;

class LastnameErrorEmptyException extends Exception
{

  const LASTNAME_MESSAGE_ERROR_EMPTY = "Ce champ ne peut être vide !";
}

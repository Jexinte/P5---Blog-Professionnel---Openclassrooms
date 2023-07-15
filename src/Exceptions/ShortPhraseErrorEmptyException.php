<?php

namespace Exceptions;

use Exception;

class ShortPhraseErrorEmptyException extends Exception
{
  const SHORT_PHRASE_MESSAGE_ERROR_EMPTY = "Ce champ ne peut être vide !";
}

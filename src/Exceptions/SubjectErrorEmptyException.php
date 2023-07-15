<?php

namespace Exceptions;

use Exception;

class SubjectErrorEmptyException extends Exception
{

  const SUBJECT_MESSAGE_ERROR_EMPTY = "Ce champ ne peut être vide !";
}

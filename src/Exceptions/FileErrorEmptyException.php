<?php

namespace Exceptions;

use Exception;

class FileErrorEmptyException extends Exception
{
  const FILE_MESSAGE_ERROR_NO_FILE_SELECTED = "Veuillez sélectionner un fichier !";
}

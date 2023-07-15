<?php

namespace Exceptions;

use Exception;

class FileTypeException extends Exception
{
  const FILE_MESSAGE_ERROR_TYPE_FILE = "Seuls les fichiers de type : jpg, jpeg , png et webp sont acceptés !";
}

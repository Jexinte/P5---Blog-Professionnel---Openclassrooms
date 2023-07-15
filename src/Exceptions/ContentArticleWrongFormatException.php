<?php

namespace Exceptions;

use Exception;

class ContentArticleWrongFormatException extends Exception
{
  const CONTENT_ARTICLE_MESSAGE_ERROR_MAX_5000_CHARS = "Le contenu doit commencer par une majuscule et ne peut excéder 5000 caractères !";
}

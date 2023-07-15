<?php

namespace Exceptions;

use Exception;

class ContentArticleErrorEmptyException extends Exception
{
  const CONTENT_ARTICLE_MESSAGE_ERROR_EMPTY = "Ce champ ne peut être vide !";
}

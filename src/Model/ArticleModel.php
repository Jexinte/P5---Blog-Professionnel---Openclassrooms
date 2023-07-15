<?php

namespace Model;

class ArticleModel
{

  public function __construct(
    public string $image,
    public string $title,
    public string $chapo,
    public string $content,
    public array  $tags,
    public string $author,
    public string $dateCreation
  ) {
  }
}

<?php

namespace Model;

use Config\DatabaseConnection;
use DateTime;
use Exception;
use IntlDateFormatter;

class Article
{

  public function __construct(private readonly DatabaseConnection   $connector)
  {
  }


  public function getArticles(): array
  {

    $dbConnect = $this->connector->connect();

    $statement = $dbConnect->prepare("SELECT id,title,chapô,content,tags,author,DATE_FORMAT(date_creation,'%d %M %Y') AS date_article  FROM article ORDER BY id DESC");
    $statement->execute();

    $articles = [];


    while ($row = $statement->fetch()) {
      $frenchDateFormat = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);

      $date = $frenchDateFormat->format(new DateTime($row["date_article"]));
      $statement2 = $dbConnect->prepare("SELECT profile_image AS image, username FROM user WHERE username = :author");
      $statement2->bindParam("author", $row["author"]);
      $statement2->execute();
      while ($row2 = $statement2->fetch()) {
        $data = [
          "id" => $row["id"],
          "image" => $row2['image'],
          "title" => $row['title'],
          "short_phrase" => $row['chapô'],
          "content" => substr($row['content'], 0, 250) . '...',
          "tags" => $row['tags'],
          "author" => $row['author'],
          "date_of_publication" => ucfirst($date)
        ];
      }
      $articles[] = $data;
    }



    return $articles;
  }

  /**
   * @throws Exception
   */
  public function getArticle(int $id): array
  {
    $dbConnect = $this->connector->connect();
    $statement = $dbConnect->prepare("SELECT id, image,title,chapô,content,tags,author,DATE_FORMAT(date_creation,'%d %M %Y') AS date_article FROM article WHERE id = :id");
    $statement->bindParam("id", $id);
    $statement->execute();
    $article = [];
    while ($row = $statement->fetch()) {
      $french_date_format = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);

      $date = $french_date_format->format(new DateTime($row["date_article"]));
      $statement2 = $dbConnect->prepare("SELECT profile_image, username FROM user WHERE username = :author");
      $statement2->bindParam("author", $row["author"]);
      $statement2->execute();
      while ($row2 = $statement2->fetch()) {

        $data = [
          "id" => $row["id"],
          "image" => $row['image'],
          "author_image" => $row2["profile_image"],
          "title" => $row['title'],
          "short_phrase" => $row['chapô'],
          "content" => $row["content"],
          "tags" => $row['tags'],
          "author" => $row['author'],
          "date_of_publication" => ucfirst($date)
        ];
      }
      $article[] = $data;
    }
    header("HTTP/1.1 200");
    return $article;
  }

  public function createArticle(array $articleData, array $sessionData): void
  {

    $dbConnect = $this->connector->connect();
    $idSession = $sessionData["session_id"];
    $usernameSession = $sessionData["username"];
    $typeUserSession = $sessionData["type_user"];

    $statementSession = $dbConnect->prepare("SELECT id_session,username,user_type FROM session WHERE id_session = :id_from_session_variable AND username = :username_from_session_variable AND user_type = :type_user_from_session_variable");

    $statementSession->bindParam("id_from_session_variable", $idSession);
    $statementSession->bindParam("username_from_session_variable", $usernameSession);
    $statementSession->bindParam("type_user_from_session_variable", $typeUserSession);

    $statementSession->execute();

    if ($statementSession) {
      echo "Une correspondance à été trouvé !";
      $titleArticle = $articleData["title"]["title"];
      $fileArticle = $articleData["file"]["file"];
      $shortPhraseArticle = $articleData["short_phrase"]["short_phrase"];
      $contentArticle = $articleData["content"]["content"];
      $tagsArticle = $articleData["tags"]["tags"];

      $fileRequirements = explode(';', $fileArticle);
      $fileSettings["file_name"] = $fileRequirements[0];
      $fileSettings["tmp_name"] = $fileRequirements[1];
      $fileSettings["directory"] = $fileRequirements[2];
      $filePath = "http://localhost/P5_Créez votre premier blog en PHP - Dembele Mamadou/public/assets/images/" . $fileSettings["file_name"];

      $statementArticle = $dbConnect->prepare("INSERT INTO article (image,title,chapô,content,tags,author,date_creation) VALUES(:fileArticle,:titleArticle,:shortPhraseArticle,:contentArticle,:tagsArticle,:authorArticle,:dateArticle)");

      $statementArticle->bindParam(':fileArticle', $filePath);
      $statementArticle->bindParam(':titleArticle', $titleArticle);
      $statementArticle->bindParam(':shortPhraseArticle', $shortPhraseArticle);
      $statementArticle->bindParam(':contentArticle', $contentArticle);
      $statementArticle->bindParam(':tagsArticle', $tagsArticle);
      $statementArticle->bindParam(':authorArticle', $usernameSession);
      $statementArticle->bindValue(':dateArticle', date('Y-m-d'));
      $statementArticle->execute();
      move_uploaded_file($fileSettings["tmp_name"], $fileSettings["directory"] . "/" . $fileSettings["file_name"]);
      header("HTTP/1.1 302");
      header("Location: index.php?selection=admin_panel");
    }
  }
}

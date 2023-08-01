<?php

namespace Exceptions;

use Exception;

class ValidationException extends Exception
{
  protected array $errors = [];
  const ERROR_EMPTY = "Ce champ ne peut être vide !";
  const COMMENT_WRONG_FORMAT_EXCEPTION = "Un commentaire doit commencer par une lettre majuscule et ne peut excéder 500 caractères";
  const CONTENT_ARTICLE_MESSAGE_ERROR_MAX_5000_CHARS = "Le contenu doit commencer par une majuscule et ne peut excéder 5000 caractères !";
  const CONTENT_MESSAGE_ERROR_MIN_20_CHARS_MAX_500_CHARS = "Le texte doit contenir minimum 20 caractères et ne peut en excéder 500 !";
  const EMAIL_UNAVAILABLE_MESSAGE_ERROR = "L'adresse e-mail ";
  const EMAIL_UNEXIST_MESSAGE_ERROR = "Oups ! Nous n'avons trouvé aucun compte associé à cette adresse e-mail. Assurez-vous que vous avez saisi correctement votre adresse e-mail et réessayez";
  const EMAIL_MESSAGE_ERROR_WRONG_FORMAT = "Oops! Le format de votre saisie est incorrect, merci de suivre le format requis : nomadressemail@domaine.extension";
  const FILE_MESSAGE_ERROR_NO_FILE_SELECTED = "Veuillez sélectionner un fichier !";
  const FILE_MESSAGE_ERROR_TYPE_FILE = "Seuls les fichiers de type : jpg, jpeg , png et webp sont acceptés !";
  const FIRSTNAME_MESSAGE_ERROR_WRONG_FORMAT = "La première lettre de votre prénom doit être en majuscule et ne doit pas contenir de caractères spéciaux tels que les éléments suivant : ?,!, etc... ";
  const MESSAGE_SENT_FAILED = "Votre message n'a pu être envoyé , veuillez réessayez plus tard !";
  const LASTNAME_MESSAGE_ERROR_WRONG_FORMAT = "La première lettre de votre nom doit être en majuscule et ne doit pas contenir de caractères spéciaux tels que les éléments suivant : ?,!, etc... ";
  const PASSWORD_INCORRECT_MESSAGE_ERROR = "Le mot de passe est incorrect !";
  const PASSWORD_MESSAGE_ERROR_WRONG_FORMAT = "Oops! Le format de votre mot de passe est incorrect, merci de suivre le format ci-dessous :";
  const SHORT_PHRASE_MESSAGE_ERROR_MAX_255_CHARS = "Le chapô doit commencer par une majuscule et ne peut excéder 255 caractères !";
  const SUBJECT_MESSAGE_ERROR_MIN_20_CHARS_MAX_100_CHARS = "Le texte doit contenir minimum 20 caractères et ne peut en excéder 100 !";
  const TAGS_MESSAGE_ERROR_MAX_3_TAGS = "Le nombres de tags maximum de tag autorisé dans un article est limité à 3. Chaque nouveau tag doit commencer par un # suivi d'une majuscule !";
  const TITLE_MESSAGE_ERROR_MAX_255_CHARS = "Le titre doit commencer par une majuscule et ne peut excéder 255 caractères !";
  const USERNAME_UNAVAILABLE_MESSAGE_ERROR = "Le nom d'utilisateur ";
  const USERNAME_MESSAGE_ERROR_WRONG_FORMAT = "Oops ! Merci de suivre le format ci-dessous pour votre nom d'utilisateur !";
  const EXPLANATION_MESSAGE_ERROR_WRONG_FORMAT = "L'explication doit commencer par une lettre majuscule et ne peut excéder 500 caractères";

  public function setTypeAndValueOfException($key, $message)
  {
    $this->errors[$key] = $message;
    return $this;
  }

  public function getErrors()
  {
    return $this->errors;
  }
}
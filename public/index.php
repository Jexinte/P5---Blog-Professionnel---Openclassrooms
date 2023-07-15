<?php


require_once __DIR__ . "../../vendor/autoload.php";

use Model\Article;
use Model\User;
use Model\HomepageForm;
use Model\SessionManager;

$sessionRepository = new SessionManager();
$sessionRepository->startSession();

use Config\DatabaseConnection;

use Exceptions\UsernameErrorEmptyException;
use Exceptions\UsernameWrongFormatException;
use Exceptions\FileErrorEmptyException;
use Exceptions\FileTypeException;
use Exceptions\EmailErrorEmptyException;
use Exceptions\EmailWrongFormatException;
use Exceptions\PasswordErrorEmptyException;
use Exceptions\PasswordWrongFormatException;
use Exceptions\TitleErrorEmptyException;
use Exceptions\TitleWrongFormatException;
use Exceptions\ContentArticleErrorEmptyException;
use Exceptions\ContentArticleWrongFormatException;
use Exceptions\ShortPhraseErrorEmptyException;
use Exceptions\ShortPhraseWrongFormatException;
use Exceptions\TagsErrorEmptyException;
use Exceptions\TagsWrongFormatException;
use Exceptions\ContentMessageWrongFormatException;
use Exceptions\ContentMessageErrorEmptyException;
use Exceptions\SubjectErrorEmptyException;
use Exceptions\SubjectWrongFormatException;
use Exceptions\LastnameErrorEmptyException;
use Exceptions\LastnameWrongFormatException;
use Exceptions\FirstNameErrorEmptyException;
use Exceptions\FirstNameWrongFormatException;

use Controller\ArticleController;
use Controller\UserController;
use Controller\DownloadController;
use Controller\HomepageFormController;





$action = "";
$selection = "";

$paths = [
    __DIR__ . "/../templates",
    __DIR__ . '/../templates/admin'
];
$loader = new \Twig\Loader\FilesystemLoader($paths);
$twig = new \Twig\Environment(
    $loader,
    [
        'cache' => false
    ]
);
$db = new DatabaseConnection("professional_blog", "root", "");


$userRepository = new User($db);
$userController = new UserController($userRepository);

$articleRepository = new Article($db);
$articleController = new ArticleController($articleRepository);

$downloadController = new DownloadController();

$formRepository = new HomepageForm($db);
$formController = new HomepageFormController($formRepository);


$template = "homepage.twig";
$paramaters = [];
if (isset($_GET['action'])) {

    $action = $_GET['action'];


    switch ($action) {
        case "sign_up":
            try {
                $template = "sign_up.twig";

                $paramaters["message"] = $userController->signUpValidator(
                    $_POST['username'],
                    $_FILES['profile_image'],
                    $_POST["mail"],
                    $_POST["password"]
                );
            } catch (UsernameErrorEmptyException $e) {
                $paramaters["username_exception"] =
                    UsernameErrorEmptyException::USERNAME_MESSAGE_ERROR_EMPTY;
            } catch (UsernameWrongFormatException $e) {
                $paramaters["username_exception"] = UsernameWrongFormatException::USERNAME_MESSAGE_ERROR_WRONG_FORMAT;
            } catch (FileErrorEmptyException $e) {
                $paramaters["file_exception"] = FileErrorEmptyException::FILE_MESSAGE_ERROR_NO_FILE_SELECTED;
            } catch (FileTypeException $e) {
                $paramaters["file_exception"] = FileTypeException::FILE_MESSAGE_ERROR_TYPE_FILE;
            } catch (EmailErrorEmptyException $e) {
                $paramaters["email_exception"] = EmailErrorEmptyException::EMAIL_MESSAGE_ERROR_EMPTY;
            } catch (EmailWrongFormatException $e) {
                $paramaters["email_exception"] = EmailWrongFormatException::EMAIL_MESSAGE_ERROR_WRONG_FORMAT;
            } catch (PasswordErrorEmptyException $e) {
                $paramaters["password_exception"] = PasswordErrorEmptyException::PASSWORD_MESSAGE_ERROR_EMPTY;
            } catch (PasswordWrongFormatException $e) {
                $paramaters["password_exception"] = PasswordWrongFormatException::PASSWORD_MESSAGE_ERROR_WRONG_FORMAT;
            }

            break;
        case "sign_in":
            try {
                $template = "sign_in.twig";

                $paramaters["message"] = $userController->loginValidator($_POST['mail'], $_POST["password"]);
                $login = $userController->loginValidator($_POST['mail'], $_POST["password"]);

                if (is_array($login) && array_key_exists("username", $login) && array_key_exists("type_user", $login)) {
                    $_SESSION["username"] = $login["username"];
                    $_SESSION["type_user"] = $login["type_user"];
                    $userController->handleInsertSessionData($_SESSION);
                }
            } catch (EmailErrorEmptyException $e) {
                $paramaters["email_exception"] = EmailErrorEmptyException::EMAIL_MESSAGE_ERROR_EMPTY;
            } catch (EmailWrongFormatException $e) {
                $paramaters["email_exception"] = EmailWrongFormatException::EMAIL_MESSAGE_ERROR_WRONG_FORMAT;
            } catch (PasswordErrorEmptyException $e) {
                $paramaters["password_exception"] = PasswordErrorEmptyException::PASSWORD_MESSAGE_ERROR_EMPTY;
            } catch (PasswordWrongFormatException $e) {
                $paramaters["password_exception"] = PasswordWrongFormatException::PASSWORD_MESSAGE_ERROR_WRONG_FORMAT;
            }

            break;

        case "download_file":
            $template = "homepage.twig";
            $downloadController->handleDownloadFile();
            break;

        case "contact":
            try {
                $template = "homepage.twig";
                $paramaters["message"] = $formController->homepageFormValidator($_POST["firstname"], $_POST["lastname"], $_POST["mail"], $_POST["subject"], $_POST["message"]);
            } catch (FirstNameErrorEmptyException $e) {
                $paramaters["firstname_exception"] = FirstNameErrorEmptyException::FIRSTNAME_MESSAGE_ERROR_EMPTY;
            } catch (FirstNameWrongFormatException $e) {
                $paramaters["firstname_exception"] = FirstNameWrongFormatException::FIRSTNAME_MESSAGE_ERROR_WRONG_FORMAT;
            } catch (LastnameErrorEmptyException $e) {
                $paramaters["lastname_exception"] = LastnameErrorEmptyException::LASTNAME_MESSAGE_ERROR_EMPTY;
            } catch (LastnameWrongFormatException $e) {
                $paramaters["lastname_exception"] = LastnameWrongFormatException::LASTNAME_MESSAGE_ERROR_WRONG_FORMAT;
            } catch (EmailErrorEmptyException $e) {
                $paramaters["email_exception"] = EmailErrorEmptyException::EMAIL_MESSAGE_ERROR_EMPTY;
            } catch (EmailWrongFormatException $e) {
                $paramaters["email_exception"] = EmailWrongFormatException::EMAIL_MESSAGE_ERROR_WRONG_FORMAT;
            } catch (SubjectErrorEmptyException $e) {
                $paramaters["subject_exception"] = SubjectErrorEmptyException::SUBJECT_MESSAGE_ERROR_EMPTY;
            } catch (SubjectWrongFormatException $e) {
                $paramaters["subject_exception"] = SubjectWrongFormatException::SUBJECT_MESSAGE_ERROR_MIN_20_CHARS_MAX_100_CHARS;
            } catch (ContentMessageErrorEmptyException $e) {
                $paramaters["content_message_exception"] = ContentMessageErrorEmptyException::CONTENT_MESSAGE_ERROR_EMPTY;
            } catch (ContentMessageWrongFormatException $e) {
                $paramaters["content_message_exception"] = ContentMessageWrongFormatException::CONTENT_MESSAGE_ERROR_MIN_20_CHARS_MAX_500_CHARS;
            }
            break;

        case "error":
            if (isset($_GET["code"])) {
                $template = "error.twig";
                $paramaters["code"] = $_GET["code"];
            }
            break;

        case "add_article":
            $template = "admin_add_article.twig";
            try {

                $paramaters = [
                    "article" => $articleController->handleCreateArticleValidator($_POST["title"], $_FILES["image_file"], $_POST["short-phrase"], $_POST["content"], $_POST["tags"], $_SESSION),
                    "session" => $_SESSION,

                ];
            } catch (TitleErrorEmptyException $e) {
                $paramaters["title_exception"] = TitleErrorEmptyException::TITLE_MESSAGE_ERROR_EMPTY;
            } catch (TitleWrongFormatException $e) {
                $paramaters["title_exception"] = TitleWrongFormatException::TITLE_MESSAGE_ERROR_MAX_51_CHARS;
            } catch (FileErrorEmptyException $e) {
                $paramaters["file_exception"] = FileErrorEmptyException::FILE_MESSAGE_ERROR_NO_FILE_SELECTED;
            } catch (FileTypeException $e) {
                $paramaters["file_exception"] = FileTypeException::FILE_MESSAGE_ERROR_TYPE_FILE;
            } catch (ShortPhraseErrorEmptyException $e) {
                $paramaters["short_phrase_exception"] = ShortPhraseErrorEmptyException::SHORT_PHRASE_MESSAGE_ERROR_EMPTY;
            } catch (ShortPhraseWrongFormatException $e) {
                $paramaters["short_phrase_exception"] = ShortPhraseWrongFormatException::SHORT_PHRASE_MESSAGE_ERROR_MAX_200_CHARS;
            } catch (ContentArticleErrorEmptyException $e) {
                $paramaters["content_article_exception"] = ContentArticleErrorEmptyException::CONTENT_ARTICLE_MESSAGE_ERROR_EMPTY;
            } catch (ContentArticleWrongFormatException $e) {
                $paramaters["content_article_exception"] = ContentArticleWrongFormatException::CONTENT_ARTICLE_MESSAGE_ERROR_MAX_5000_CHARS;
            } catch (TagsErrorEmptyException $e) {
                $paramaters["tags_exception"] = TagsErrorEmptyException::TAGS_ERROR_EMPTY;
            } catch (TagsWrongFormatException $e) {
                $paramaters["tags_exception"] = TagsWrongFormatException::TAGS_MESSAGE_ERROR_MAX_3_TAGS;
            }
            break;

        case "logout":
            $logout = $userController->handleLogout($_SESSION);
            if (is_array($logout) && array_key_exists("logout", $logout) && $logout["logout"] == 1) $sessionRepository->destroySession();
            break;
    }
} elseif ((isset($_GET['selection']))) {

    $selection = $_GET['selection'];

    switch ($selection) {

        case "homepage":
            $template = "homepage.twig";
            $paramaters["session"] =  $_SESSION;
            break;
        case "sign_in":
            $template = "sign_in.twig";
            break;
        case "sign_up":
            $template = "sign_up.twig";
            break;
        case "blog":
            $template = "blog.twig";

            if (is_array($userController->handleGetIdSessionData($_SESSION)) && array_key_exists("session_id", $userController->handleGetIdSessionData($_SESSION))) {
                $_SESSION["session_id"] = $userController->handleGetIdSessionData($_SESSION)["session_id"];
            }

            $paramaters = [
                "articles" => $articleController->listOfAllArticles(),
                "session" => $_SESSION
            ];
            break;
        case "admin_panel":
            $template = "admin_homepage.twig";
            $paramaters = [
                "session" => $_SESSION
            ];
            break;
        case "view_article":
            $template = "admin_article_and_commentary.twig";
            break;
        case "article":


            $template = "article.twig";
            $paramaters["article"] = $articleController->handleOneArticle($_GET['id']);
            break;
        case "add_article":
            $template = "admin_add_article.twig";
            $paramaters["session"] =  $_SESSION;
            break;
        case "update_article":
            $template = "admin_update_article.twig";
            break;
    }
}

echo $twig->render($template, $paramaters);

<?php

require_once __DIR__ . "../../vendor/autoload.php";

use Config\DatabaseConnection;
use Repository\SessionManagerRepository;

$db = new DatabaseConnection("professional_blog", "root", "");

$sessionRepository = new SessionManagerRepository($db);
$sessionRepository->startSession();


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
use Exceptions\CommentEmptyException;
use Exceptions\CommentWrongFormatException;
use Exceptions\EmailUnavailableException;
use Exceptions\EmailUnexistException;
use Exceptions\FormMessageNotSentException;
use Exceptions\PasswordIncorrectException;
use Exceptions\UsernameUnavailableException;
use Exceptions\ValidationErrorWrongFormatException;

use Controller\ArticleController;
use Controller\UserController;
use Controller\DownloadController;
use Controller\HomepageFormController;
use Controller\TemporaryCommentController;
use Controller\CommentController;
use Controller\NotificationController;
use Controller\SessionController;

use Enumeration\UserType;

use Repository\ArticleRepository;
use Repository\CommentRepository;
use Repository\HomepageFormRepository;
use Repository\NotificationRepository;
use Repository\TemporaryCommentRepository;
use Repository\UserRepository;

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
$formCredentialUsername = file_get_contents("../config/stmp_credentials.json");
$formCredentialsPassword = file_get_contents("../config/stmp_credentials.json");
$formCredentialsSmtpAddress = file_get_contents("../config/stmp_credentials.json");

$userRepository = new UserRepository($db);
$userController = new UserController($userRepository);

$sessionRepository = new SessionManagerRepository($db);
$sessionController = new SessionController($sessionRepository);

$articleRepository = new ArticleRepository($db);
$articleController = new ArticleController($articleRepository);

$downloadController = new DownloadController();

$formRepository = new HomepageFormRepository(
    $db,
    $formCredentialUsername,
    $formCredentialsPassword,
    $formCredentialsSmtpAddress
);
$formController = new HomepageFormController($formRepository);



$temporaryCommentRepository = new TemporaryCommentRepository(
    $db,
    $formCredentialUsername,
    $formCredentialsPassword,
    $formCredentialsSmtpAddress
);
$temporaryCommentController = new TemporaryCommentController($temporaryCommentRepository);

$commentRepository = new CommentRepository($db);
$commentController = new CommentController($commentRepository);

$notificationRepository = new NotificationRepository($db);
$notificationController = new NotificationController($notificationRepository);

$template = "homepage.twig";
$paramaters = [];


if (isset($_GET['action'])) {

    $action = $_GET['action'];
    $defaultValues = [];

    $defaultValuesTemporaryComments = [];
    $labels = [
        "title", "id",
        "short_phrase",
        "content",
        "image",
        "author_image",
        "author",
        "tags",
        "date_of_publication"
    ];
    $labelsUpdateArticle = [
        "title",
        "short_phrase",
        "content",
        "tags",
        "file_image",
        "id_article"
    ];

    $labelsTemporaryComments = [
        "username", "date_of_publication", "content"
    ];

    switch ($action) {
        case "sign_up":
            try {
                $template = "sign_up.twig";
                $signUp = $userController->signUpValidator(
                    $_POST['username'],
                    $_FILES['profile_image'],
                    $_POST["mail"],
                    $_POST["password"]
                );

                $paramaters["message"] = $signUp;

                if (is_null($signUp)) {
                    header("HTTP/1.1 302");
                    header("Location: index.php?selection=sign_in");
                }
            } catch (UsernameErrorEmptyException $e) {
                header("HTTP/1.1 400");
                $paramaters["username_exception"] =
                    UsernameErrorEmptyException::USERNAME_MESSAGE_ERROR_EMPTY;
            } catch (UsernameWrongFormatException $e) {
                header("HTTP/1.1 400");
                $paramaters["username_exception"] = UsernameWrongFormatException::USERNAME_MESSAGE_ERROR_WRONG_FORMAT;
            } catch (FileErrorEmptyException $e) {
                header("HTTP/1.1 400");
                $paramaters["file_exception"] = FileErrorEmptyException::FILE_MESSAGE_ERROR_NO_FILE_SELECTED;
            } catch (FileTypeException $e) {
                header("HTTP/1.1 400");
                $paramaters["file_exception"] = FileTypeException::FILE_MESSAGE_ERROR_TYPE_FILE;
            } catch (EmailErrorEmptyException $e) {
                header("HTTP/1.1 400");
                $paramaters["email_exception"] = EmailErrorEmptyException::EMAIL_MESSAGE_ERROR_EMPTY;
            } catch (EmailWrongFormatException $e) {
                header("HTTP/1.1 400");
                $paramaters["email_exception"] = EmailWrongFormatException::EMAIL_MESSAGE_ERROR_WRONG_FORMAT;
            } catch (PasswordErrorEmptyException $e) {
                header("HTTP/1.1 400");
                $paramaters["password_exception"] = PasswordErrorEmptyException::PASSWORD_MESSAGE_ERROR_EMPTY;
            } catch (PasswordWrongFormatException $e) {
                header("HTTP/1.1 400");
                $paramaters["password_exception"] = PasswordWrongFormatException::PASSWORD_MESSAGE_ERROR_WRONG_FORMAT;
            } catch (UsernameUnavailableException $e) {
                header("HTTP/1.1 400");
                $paramaters["username_exception"] = UsernameUnavailableException::USERNAME_UNAVAILABLE_MESSAGE_ERROR . ' ' . $_POST["username"] . " n'est pas disponible ";
            } catch (EmailUnavailableException $e) {
                header("HTTP/1.1 400");
                $paramaters["email_exception"] = EmailUnavailableException::EMAIL_UNAVAILABLE_MESSAGE_ERROR . ' ' . $_POST["mail"] . " n'est pas disponible";
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
                    $_SESSION["id_user"] = $login["id_user"];
                    $sessionController->handleInsertSessionData($_SESSION);
                    header("HTTP/1.1 302");
                    header("Location: index.php?selection=blog");
                }
            } catch (EmailErrorEmptyException $e) {
                header("HTTP/1.1 400");
                $paramaters["email_exception"] = EmailErrorEmptyException::EMAIL_MESSAGE_ERROR_EMPTY;
            } catch (EmailWrongFormatException $e) {
                header("HTTP/1.1 400");
                $paramaters["email_exception"] = EmailWrongFormatException::EMAIL_MESSAGE_ERROR_WRONG_FORMAT;
            } catch (PasswordErrorEmptyException $e) {
                header("HTTP/1.1 400");
                $paramaters["password_exception"] = PasswordErrorEmptyException::PASSWORD_MESSAGE_ERROR_EMPTY;
            } catch (PasswordWrongFormatException $e) {
                header("HTTP/1.1 400");
                $paramaters["password_exception"] = PasswordWrongFormatException::PASSWORD_MESSAGE_ERROR_WRONG_FORMAT;
            } catch (EmailUnexistException $e) {
                header("HTTP/1.1 400");
                $paramaters["email_exception"] = EmailUnexistException::EMAIL_UNEXIST_MESSAGE_ERROR;
            } catch (PasswordIncorrectException $e) {
                header("HTTP/1.1 403");
                $paramaters["password_exception"] = PasswordIncorrectException::PASSWORD_INCORRECT_MESSAGE_ERROR;
            }

            break;

        case "download_file":
            $template = "homepage.twig";
            $fileIsDownload = $downloadController->handleDownloadFile();
            if (is_array($fileIsDownload) && array_key_exists("file_logs", $fileIsDownload)) {
                header("Content-Length: " . $fileIsDownload["file_logs"]);
                header('Content-Description: File Transfer');
                header("Content-Type: application/pdf");
                header("Pragma: public");
                header("Content-Disposition:attachment;filename=cv.pdf");
                header("HTTP/1.1 200");
                readfile($fileIsDownload["file_logs"]);
            } else {
                header("Location: index.php?action=error&code=" . $fileIsDownload["code_error"]);
            }
            break;

        case "contact":
            try {
                $template = "homepage.twig";
                $paramaters["message"] = $formController->homepageFormValidator($_POST["firstname"], $_POST["lastname"], $_POST["mail"], $_POST["subject"], $_POST["message"]);
            } catch (FirstNameErrorEmptyException $e) {
                header('HTTP/1.1 400');
                $paramaters["firstname_exception"] = FirstNameErrorEmptyException::FIRSTNAME_MESSAGE_ERROR_EMPTY;
            } catch (FirstNameWrongFormatException $e) {
                header('HTTP/1.1 400');
                $paramaters["firstname_exception"] = FirstNameWrongFormatException::FIRSTNAME_MESSAGE_ERROR_WRONG_FORMAT;
            } catch (LastnameErrorEmptyException $e) {
                header('HTTP/1.1 400');
                $paramaters["lastname_exception"] = LastnameErrorEmptyException::LASTNAME_MESSAGE_ERROR_EMPTY;
            } catch (LastnameWrongFormatException $e) {
                header('HTTP/1.1 400');
                $paramaters["lastname_exception"] = LastnameWrongFormatException::LASTNAME_MESSAGE_ERROR_WRONG_FORMAT;
            } catch (EmailErrorEmptyException $e) {
                header('HTTP/1.1 400');
                $paramaters["email_exception"] = EmailErrorEmptyException::EMAIL_MESSAGE_ERROR_EMPTY;
            } catch (EmailWrongFormatException $e) {
                header('HTTP/1.1 400');
                $paramaters["email_exception"] = EmailWrongFormatException::EMAIL_MESSAGE_ERROR_WRONG_FORMAT;
            } catch (SubjectErrorEmptyException $e) {
                header('HTTP/1.1 400');
                $paramaters["subject_exception"] = SubjectErrorEmptyException::SUBJECT_MESSAGE_ERROR_EMPTY;
            } catch (SubjectWrongFormatException $e) {
                header('HTTP/1.1 400');
                $paramaters["subject_exception"] = SubjectWrongFormatException::SUBJECT_MESSAGE_ERROR_MIN_20_CHARS_MAX_100_CHARS;
            } catch (ContentMessageErrorEmptyException $e) {
                header('HTTP/1.1 400');
                $paramaters["content_message_exception"] = ContentMessageErrorEmptyException::CONTENT_MESSAGE_ERROR_EMPTY;
            } catch (ContentMessageWrongFormatException $e) {
                header('HTTP/1.1 400');
                $paramaters["content_message_exception"] = ContentMessageWrongFormatException::CONTENT_MESSAGE_ERROR_MIN_20_CHARS_MAX_500_CHARS;
            } catch (FormMessageNotSentException $e) {
                header("HTTP/1.1 500");
                header("Location: index.php?action=error&code=500");
                $paramaters["message_not_sent_exception"] = FormMessageNotSentException::MESSAGE_SENT_FAILED;
            }
            break;

        case "error":
            if (isset($_GET["code"])) {
                $template = "error.twig";
                $paramaters["code"] = $_GET["code"];
            }
            break;

        case "add_article":

            try {
                if ($_SESSION["type_user"] == UserType::ADMIN->value) {
                    $template = "admin_add_article.twig";
                    $paramaters = [
                        "session" => $_SESSION,
                    ];
                    $articleIsCreated = $articleController->handleCreateArticleValidator($_POST["title"], $_FILES["image_file"], $_POST["short-phrase"], $_POST["content"], $_POST["tags"], $_SESSION);
                    if (is_null($articleIsCreated)) {
                        header("HTTP/1.1 302");
                        header("Location: index.php?selection=admin_panel");
                    }
                } else {
                    header("Location: index.php?action=error&code=403");
                }
            } catch (TitleErrorEmptyException $e) {
                header("HTTP/1.1 400");
                $paramaters["title_exception"] = TitleErrorEmptyException::TITLE_MESSAGE_ERROR_EMPTY;
            } catch (TitleWrongFormatException $e) {
                header("HTTP/1.1 400");
                $paramaters["title_exception"] = TitleWrongFormatException::TITLE_MESSAGE_ERROR_MAX_500_CHARS;
            } catch (FileErrorEmptyException $e) {
                header("HTTP/1.1 400");
                $paramaters["file_exception"] = FileErrorEmptyException::FILE_MESSAGE_ERROR_NO_FILE_SELECTED;
            } catch (FileTypeException $e) {
                header("HTTP/1.1 400");
                $paramaters["file_exception"] = FileTypeException::FILE_MESSAGE_ERROR_TYPE_FILE;
            } catch (ShortPhraseErrorEmptyException $e) {
                header("HTTP/1.1 400");
                $paramaters["short_phrase_exception"] = ShortPhraseErrorEmptyException::SHORT_PHRASE_MESSAGE_ERROR_EMPTY;
            } catch (ShortPhraseWrongFormatException $e) {
                header("HTTP/1.1 400");
                $paramaters["short_phrase_exception"] = ShortPhraseWrongFormatException::SHORT_PHRASE_MESSAGE_ERROR_MAX_500_CHARS;
            } catch (ContentArticleErrorEmptyException $e) {
                header("HTTP/1.1 400");
                $paramaters["content_article_exception"] = ContentArticleErrorEmptyException::CONTENT_ARTICLE_MESSAGE_ERROR_EMPTY;
            } catch (ContentArticleWrongFormatException $e) {
                header("HTTP/1.1 400");
                $paramaters["content_article_exception"] = ContentArticleWrongFormatException::CONTENT_ARTICLE_MESSAGE_ERROR_MAX_5000_CHARS;
            } catch (TagsErrorEmptyException $e) {
                header("HTTP/1.1 400");
                $paramaters["tags_exception"] = TagsErrorEmptyException::TAGS_ERROR_EMPTY;
            } catch (TagsWrongFormatException $e) {
                header("HTTP/1.1 400");
                $paramaters["tags_exception"] = TagsWrongFormatException::TAGS_MESSAGE_ERROR_MAX_3_TAGS;
            }
            break;

        case "update_article":
            try {
                if ($_SESSION["type_user"] == UserType::ADMIN->value) {
                    $originalData = [];
                    $postData = [
                        $_POST["title"], $_POST["short_phrase"], $_POST["content"], $_POST["tags"], $_POST["original_file_path"],
                        $_POST['id_article']
                    ];

                    foreach ($labelsUpdateArticle as $k => $v) {
                        $originalData[$v] = $postData[$k];
                    }

                    $template = "admin_update_article.twig";
                    $updatedData = $articleController->handleUpdateArticleValidator($_POST["title"], $_FILES["image_file"], $_POST["original_file_path"], $_POST["short_phrase"], $_POST["content"], $_POST["tags"], $_SESSION, $_POST["id_article"]);
                    $paramaters = [
                        "message" => $updatedData,
                        "original_data" => $originalData,
                    ];

                    if (is_array($updatedData)) {
                        header("HTTP/1.1 302");
                        header("Location: index.php?selection=admin_panel");
                    }
                } else {
                    header("Location: index.php?action=error&code=403");
                }
            } catch (TitleErrorEmptyException $e) {
                $paramaters["title_exception"] = TitleErrorEmptyException::TITLE_MESSAGE_ERROR_EMPTY;
                $paramaters["original_data"] = $originalData;
            } catch (TitleWrongFormatException $e) {
                $paramaters["title_exception"] = TitleWrongFormatException::TITLE_MESSAGE_ERROR_MAX_500_CHARS;
                $paramaters["original_data"] = $originalData;
            } catch (ShortPhraseErrorEmptyException $e) {
                $paramaters["short_phrase_exception"] = ShortPhraseErrorEmptyException::SHORT_PHRASE_MESSAGE_ERROR_EMPTY;
                $paramaters["original_data"] = $originalData;
            } catch (ShortPhraseWrongFormatException $e) {
                $paramaters["short_phrase_exception"] = ShortPhraseWrongFormatException::SHORT_PHRASE_MESSAGE_ERROR_MAX_500_CHARS;
                $paramaters["original_data"] = $originalData;
            } catch (ContentArticleErrorEmptyException $e) {
                $paramaters["content_exception"] = ContentArticleErrorEmptyException::CONTENT_ARTICLE_MESSAGE_ERROR_EMPTY;
                $paramaters["original_data"] = $originalData;
            } catch (ContentArticleWrongFormatException $e) {
                $paramaters["content_exception"] = ContentArticleWrongFormatException::CONTENT_ARTICLE_MESSAGE_ERROR_MAX_5000_CHARS;
                $paramaters["original_data"] = $originalData;
            } catch (TagsWrongFormatException $e) {
                $paramaters["tags_exception"] = TagsWrongFormatException::TAGS_MESSAGE_ERROR_MAX_3_TAGS;
                $paramaters["original_data"] = $originalData;
            } catch (FileTypeException $e) {
                $paramaters["file_exception"] = FileTypeException::FILE_MESSAGE_ERROR_TYPE_FILE;
                $paramaters["original_data"] = $originalData;
            }

            break;
        case "delete_article":
            if ($_SESSION["type_user"] === UserType::ADMIN->value) {
                $article = $articleController->handleDeleteArticle($_GET["id"], $_SESSION);
                if (is_array($article)) {
                    header("HTTP/1.1 302");
                    header("Location: index.php?selection=admin_panel");
                }
            } else {
                header("Location: index.php?action=error&code=403");
            }

            break;
        case "logout":
            $logout = $userController->handleLogout($_SESSION);
            if (is_array($logout)) {
                header("HTTP/1.1 302");
                header("Location: index.php?selection=blog");
                $sessionRepository->destroySession();
            }
            break;

        case "add_comment":

            try {
                if ($_SESSION["type_user"] === UserType::ADMIN->value || $_SESSION["type_user"] == UserType::USER->value) {
                    $article = current($articleController->handleOneArticle($_GET["idArticle"]));
                    foreach ($labels as $k => $v) {
                        $defaultValues[$v] = $article[$v];
                    }
                    $template = "article.twig";

                    $paramaters["default"] = $defaultValues;
                    $temporaryComment = $temporaryCommentController->handleInsertTemporaryCommentValidator($_POST["comment"], $_POST["id_article"], $_SESSION);
                    if (is_null($temporaryComment)) {
                        header("HTTP/1.1 302");
                        header("Location: index.php?selection=article&id=" . $defaultValues["id"]);
                        $temporaryCommentController->handleMailToAdmin($_SESSION, $defaultValues["title"]);
                    }
                } else {
                    header("Location: index.php?action=error&code=403");
                }
            } catch (CommentEmptyException $e) {
                $paramaters = [
                    "comment_exception" => CommentEmptyException::COMMENT_EMPTY_EXCEPTION,
                    "default" => $defaultValues
                ];
            } catch (CommentWrongFormatException $e) {
                $paramaters = [
                    "comment_exception" => CommentWrongFormatException::COMMENT_WRONG_FORMAT_EXCEPTION,
                    "default" => $defaultValues
                ];
            }
            break;
        case "validation":

            try {
                if ($_SESSION["type_user"] === UserType::ADMIN->value) {
                    $template = "admin_validation_commentary.twig";

                    $defaultValues = [];
                    $defaultValues["idComment"] = $_GET["idComment"];
                    $temporaryComment = $temporaryCommentController->handleGetOneTemporaryComment($defaultValues["idComment"]);

                    foreach ($labelsTemporaryComments as $k => $v) {
                        $defaultValuesTemporaryComments[$v] = $temporaryComment[$v];
                    }

                    $paramaters["comment"] = $defaultValues;
                    $paramaters["default"] = $temporaryCommentController->handleGetOneTemporaryComment($defaultValues["idComment"]);


                    $finalPost = isset($_POST["accepted"]) ? $_POST["accepted"] : $_POST["rejected"];

                    $validation = $temporaryCommentController->handleValidationTemporaryComment($finalPost, $_GET['idComment'], $_POST["feedback"]);
                    $notation = array_key_exists("approved", $validation)  ? "approved" : "rejected";
                    switch (true) {
                        case str_contains("approved", $notation):
                            header("HTTP/1.1 302");
                            header("Location: index.php?selection=admin_panel");
                            $_SESSION["approved"] = 1;
                            $_SESSION["id_comment"] = $validation["id_comment"];
                            $_SESSION["id_article"] = $validation["id_article"];
                            $_SESSION["id_user"] = $validation["id_user"];
                            $_SESSION["feedback"] = $validation["feedback"];
                            $notificationController->handleCreateNotification($_SESSION);
                            $commentController->handleCreateComment($validation, $_SESSION);
                            $keys = ["approved", "id_comment", "id_article", "feedback"];

                            foreach ($keys as $key) {
                                unset($_SESSION[$key]);
                            }
                            break;

                        case str_contains("rejected", $notation):
                            header("HTTP/1.1 302");
                            header("Location: index.php?selection=admin_panel");
                            $_SESSION["rejected"] = 1;
                            $_SESSION["id_comment"] = $validation["id_comment"];
                            $_SESSION["id_article"] = $validation["id_article"];
                            $_SESSION["feedback"] = $validation["feedback"];
                            $notificationController->handleCreateNotification($_SESSION);
                            $temporaryCommentController->handleDeleteTemporaryComment($validation);
                            $keys = ["rejected", "id_comment", "id_article", "feedback"];

                            foreach ($keys as $key) {
                                unset($_SESSION[$key]);
                            }
                            break;
                    }
                } else {
                    header("Location: index.php?action=error&code=403");
                }
            } catch (ValidationErrorWrongFormatException $e) {
                $paramaters = [
                    "validation_exception" => ValidationErrorWrongFormatException::VALIDATION_MESSAGE_ERROR_WRONG_FORMAT,
                    "comment" => $temporaryComment
                ];
            }
            break;

        case "delete_notification":
            if ($_SESSION["type_user"] === UserType::USER->value) {
                $template = "notification.twig";
                $paramaters["notifications"] = $notificationController->handleGetAllUserNotifications($_SESSION);
                $article = $notificationController->handleDeleteNotification($_GET["id_notification"]);
                if (is_null($article)) {
                    header("HTTP/1.1 302");
                    header("Location:index.php?selection=notifications");
                }
            } else {
                header("Location: index.php?action=error&code=403");
            }
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
            $sessionCreated = $sessionController->handleGetIdSessionData($_SESSION);
            if (is_array($sessionCreated) && array_key_exists("session_id", $sessionCreated)) {
                $_SESSION["session_id"] = $sessionCreated["session_id"];
            }

            $totalNotifications = $userController->handleGetAllUserNotifications($_SESSION);

            $paramaters = [
                "articles" => $articleController->listOfAllArticles(),
                "session" => $_SESSION,
                "total_notifications" => count($totalNotifications)
            ];
            break;
        case "admin_panel":
            if ($_SESSION["type_user"] == UserType::ADMIN->value) {
                $template = "admin_homepage.twig";
                $totalTemporaryComment =  $temporaryCommentController->handlegetTemporaryCommentsForAdministrators($_SESSION);
                $paramaters = [
                    "articles" => $articleController->listOfAllArticles(),
                    "session" => $_SESSION,
                    "comments" => $totalTemporaryComment,
                    "total_comments" => is_array($totalTemporaryComment) ? count($totalTemporaryComment) : 0
                ];
            } else {
                header("Location: index.php?action=error&code=403");
            }
            break;
        case "comment_details":
            if ($_SESSION["type_user"] == UserType::ADMIN->value) {
                $template = "admin_validation_commentary.twig";
                $theTemporaryComment = $temporaryCommentController->handleGetOneTemporaryComment($_GET["idComment"]);
                if (is_array($theTemporaryComment)) {
                    $paramaters["comment"] = $theTemporaryComment;
                }
            } else {
                header("Location: index.php?action=error&code=403");
            }

            break;
        case "article":
            $article = current($articleController->handleOneArticle($_GET["id"]));

            $defaultValue = ["data" => $article];

            $template = "article.twig";

            $_SESSION["id_article"] = $defaultValue["data"]["id"];
            $comments = $commentController->handleGetAllComments($_GET['id']);

            $paramaters = ["article" => $article];
            $paramaters["comments"] = $comments;

            if (isset($_SESSION["username"])) {
                $commentAlreadySentByUser = $temporaryCommentController->handlecheckCommentAlreadySentByUser($_SESSION);
                if (is_array($commentAlreadySentByUser) && array_key_exists("user_already_commented", $commentAlreadySentByUser)) {
                    $paramaters["count_of_comments"] = $commentAlreadySentByUser["user_already_commented"];
                }
            } else {
                $paramaters["no_user_connected"] = 1;
            }
            break;
        case "add_article":
            if ($_SESSION["type_user"] == UserType::ADMIN->value) {
                $template = "admin_add_article.twig";
                $paramaters["session"] =  $_SESSION;
            } else {
                header("Location: index.php?action=error&code=403");
            }

            break;
        case "view_update_article":
            if ($_SESSION["type_user"] == UserType::ADMIN->value) {
                $template = "admin_update_article.twig";
                $article = current($articleController->handleOneArticle($_GET["id"]));
                $paramaters = [
                    "article" => $article,
                ];
            } else {
                header("Location: index.php?action=error&code=403");
            }

            break;

        case "notifications":
            $notifications = $userController->handleGetAllUserNotifications($_SESSION);
            $template = "notification.twig";
            $paramaters["notifications"] = $notifications;
    }
}

echo $twig->render($template, $paramaters);

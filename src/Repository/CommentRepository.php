<?php

/**
 * Handle comments functions
 * 
 * PHP version 8
 *
 * @category Repository
 * @package  CommentRepository
 * @author   Yokke <mdembelepro@gmail.com>
 * @license  ISC License
 * @link     https://github.com/Jexinte/P5---Blog-Professionnel---Openclassrooms
 */

namespace Repository;

use Config\DatabaseConnection;
use Model\CommentModel;
use Enumeration\UserType;
use DateTime;
use IntlDateFormatter;

/**
 * CommentRepository class
 * 
 * PHP version 8
 *
 * @category Repository
 * @package  CommentRepository
 * @author   Yokke <mdembelepro@gmail.com>
 * @license  ISC License
 * @link     https://github.com/Jexinte/P5---Blog-Professionnel---Openclassrooms
 */
class CommentRepository
{

    /**
     * Summary of __construct
     *
     * @param \Config\DatabaseConnection $connector 
     */
    public function __construct(
        private readonly DatabaseConnection  $connector,
    ) {
    }

    /**
     * Summary of getAllComments
     *
     * @param array $comments 
     * @param int   $idArticle 
     * 
     * @return array
     */
    public function getAllComments(array $comments, int $idArticle): ?array
    {
        $dbConnect = $this->connector->connect();

        $statementGetComments = $dbConnect->prepare(
            "SELECT id,idArticle,idUser,content,status,
            DATE_FORMAT(dateCreation,'%d %M %Y')
             AS date_publication  
             FROM comment
              WHERE idArticle = :idArticle 
              AND status = :status 
              ORDER BY date_publication DESC"
        );
        $statementGetComments->bindParam("idArticle", $idArticle);
        $statementGetComments->bindValue("status", true);

        $statementGetComments->execute();


        while ($rowOfComment = $statementGetComments->fetch()) {
            $frenchDateFormat = new IntlDateFormatter(
                'fr_FR',
                IntlDateFormatter::FULL, 
                IntlDateFormatter::NONE
            );

            $date = $frenchDateFormat->format(
                new DateTime($rowOfComment["date_publication"])
            );
            $statementToGetUserData = $dbConnect->prepare(
                "SELECT id,username,profileImage
                 FROM user 
                 WHERE id = :idUserComment"
            );
            $statementToGetUserData->bindParam(
                "idUserComment", $rowOfComment["idUser"]
            );
            $statementToGetUserData->execute();
            while ($rowOfUserData = $statementToGetUserData->fetch()) {
                $data = [
                "username" => $rowOfUserData["username"],
                "profile_image" => $rowOfUserData["profileImage"],
                "content" => $rowOfComment['content'],
                "date_of_publication" => ucfirst($date)
                ];
            }
            $comments[] = $data;
        }

        return $comments;
    }

    /**
     * Summary of insertComment
     *
     * @param \Model\CommentModel $commentModel 
     * @param array               $sessionData 
     * @param string              $idInCookie 
     * 
     * @return CommentModel
     */
    public function insertComment(
        CommentModel $commentModel, 
        array $sessionData, 
        string $idInCookie
    ): ?CommentModel {
        $dbConnect = $this->connector->connect();
        $idSession = $sessionData["session_id"];
        $idUser = $sessionData["id_user"];
        $typeUserSession = $sessionData["type_user"];
        $statementToCheckIfActualUserSentComment = $dbConnect->prepare(
            "SELECT id,type 
            FROM user 
            WHERE id = :id_user_from_session_variable  
            AND type = :type_user_from_session_variable
        "
        );

        $statementToCheckIfActualUserSentComment->bindParam(
            "id_user_from_session_variable", 
            $idUser
        );
        $statementToCheckIfActualUserSentComment->bindParam(
            "type_user_from_session_variable", 
            $typeUserSession
        );

        $statementToCheckIfActualUserSentComment->execute();
        $user = $statementToCheckIfActualUserSentComment->fetch();
        if ($user && $idSession == $idInCookie) {

            $idArticle = $commentModel->getIdArticle();
            $idUser = $commentModel->getIdUser();
            $comment = $commentModel->getComment();
            $dateOfCreation = $commentModel->getDateCreation();


            $statementToInsertComment = $dbConnect->prepare(
                "INSERT INTO comment (idArticle,idUser,content,dateCreation) 
                VALUES(?,?,?,?)"
            );
            $values = [
            $idArticle,
            $idUser,
            $comment,
            $dateOfCreation
            ];
            $statementToInsertComment->execute($values);
            $commentModel->isCreated(true);
            return $commentModel;
        }
    }




    /**
     * Summary of checkCommentAlreadySentByUser
     *
     * @param array  $sessionData 
     * @param string $idInCookie 
     * 
     * @return array<int>|null
     */
    public function checkCommentAlreadySentByUser(
        array $sessionData, 
        string $idInCookie
    ): ?array {

        $dbConnect = $this->connector->connect();
        $idSession = $sessionData["session_id"];
        $idUserSession = $sessionData["id_user"];
        $idArticleSession = $sessionData["id_article"];


        if ($idInCookie == $idSession) {

            $statementComments = $dbConnect->prepare(
                "SELECT idArticle,idUser,status 
                FROM comment 
                WHERE idUser = :idUser AND idArticle = :idArticle 
                AND status is NULL"
            );
            $statementComments->bindParam(
                "idUser", 
                $idUserSession
            );
            $statementComments->bindParam(
                "idArticle", 
                $idArticleSession
            );
            $statementComments->execute();


            $userComments = [];
            while ($row = $statementComments->fetch()) {
                $userComments[] = $row;
            }
            switch (true) {
            case empty($userComments):
                return null;
            case array_key_exists("status", current($userComments))
                && current($userComments)["status"] === null:
                return count($userComments) == 1 ? 
                ["user_already_commented" => 1] : null;
            default:
                return null;
            }
        }
    }


    /**
     * Summary of getCommentsNotValidateByAdministrators
     *
     * @param array  $sessionData 
     * @param string $idInCookie 
     * 
     * @return array<array>|null
     */
    public function getCommentsNotValidateByAdministrators(
        array $sessionData, 
        string $idInCookie
    ): ?array {
        $dbConnect = $this->connector->connect();


        if ($sessionData["type_user"] === UserType::ADMIN->value 
            && $sessionData["session_id"] == $idInCookie
        ) {


            $comments = [];
            $statementComments = $dbConnect->prepare(
                "SELECT c.id,c.idArticle, c.idUser, c.content,c.status, 
                DATE_FORMAT(c.dateCreation, '%d %M %Y') AS date_of_publication,
                 a.title 
                 FROM comment c 
                 JOIN article a ON c.idArticle = a.id 
                 WHERE c.status IS NULL "
            );
            $statementComments->execute();


            while ($row = $statementComments->fetch()) {
                $frenchDateFormat = new IntlDateFormatter(
                    'fr_FR', 
                    IntlDateFormatter::FULL, 
                    IntlDateFormatter::NONE
                );
                $date = $frenchDateFormat->format(
                    new DateTime($row["date_of_publication"])
                );
                $statementUser = $dbConnect->prepare(
                    "SELECT id,username 
                    FROM user 
                    WHERE id = :idUser"
                );
                $statementUser->bindParam("idUser", $row["idUser"]);
                $statementUser->execute();
                while ($row2 = $statementUser->fetch()) {
                    $data = [
                        "id_article" => $row["idArticle"],
                        "id" => $row["id"],
                        "id_user" => $row["idUser"],
                        "content" => $row["content"],
                        "dateCreation" => $date,
                        "title" => $row["title"],
                        "username" => $row2["username"]
                    ];
                }

                $comments[] = $data;
            }
            return !empty($comments) ? $comments : null;
        }
    }

    /**
     * Summary of getOneComment
     *
     * @param int    $idComment 
     * @param array  $session 
     * @param string $idInCookie 
     * 
     * @return mixed
     */
    public function getOneComment(
        int $idComment, 
        array $session, 
        string $idInCookie
    ): ?array {
        $dbConnect = $this->connector->connect();

        if ($session["session_id"] == $idInCookie) {

            $statement = $dbConnect->prepare(
                "SELECT c.id, c.idUser ,idArticle,content,
                DATE_FORMAT(dateCreation, '%d %M %Y') AS date_of_publication, 
                u.username FROM comment AS c 
                JOIN user u ON c.idUser = u.id
                 WHERE c.id = :idComment"
            );
            $statement->bindParam("idComment", $idComment);
            $statement->execute();
            $thereIsComment = $statement->fetch();
            $french_date_format = new IntlDateFormatter(
                'fr_FR', 
                IntlDateFormatter::FULL, 
                IntlDateFormatter::NONE
            );

            $dateOfPublication = $french_date_format->format(
                new DateTime($thereIsComment["date_of_publication"])
            );
            $thereIsComment["date_of_publication"] = $dateOfPublication;
            if ($thereIsComment) {
                return $thereIsComment;
            }
            return null;
        }
    }

    /**
     * Summary of validationComment
     *
     * @param string $valueOfValidation 
     * @param int    $idComment 
     * @param mixed  $feedback 
     * @param array  $session 
     * @param string $idInCookie 
     * 
     * @return array
     */
    public function validationComment(
        string $valueOfValidation, 
        int $idComment, 
        ?string $feedback, 
        array $session, 
        string $idInCookie
    ): ?array {
        $dbConnect = $this->connector->connect();
        if ($session["session_id"] == $idInCookie) {

            switch (true) {
            case str_contains($valueOfValidation, "Accepter"):
                $statementToAcceptComment = $dbConnect->prepare(
                    "SELECT id,idUser,idArticle,content,
                    DATE_FORMAT(dateCreation, '%d %M %Y') AS date_of_publication 
                    FROM comment
                     WHERE id = :idComment"
                );
                $statementToAcceptComment->bindParam("idComment", $idComment);
                $statementToAcceptComment->execute();
                $resultIsAccepted = $statementToAcceptComment->fetch();
                if ($resultIsAccepted) {
                    $feedbackResult = !empty($feedback) ? $feedback : null;
                    $statementInsertAcceptedChoice = $dbConnect->prepare(
                        "UPDATE comment 
                        SET status =  :status , feedbackAdministrator = :feedback 
                        WHERE id = :idComment"
                    );
                    $statementInsertAcceptedChoice->bindValue("status", true);
                    $statementInsertAcceptedChoice->bindValue(
                        "idComment", 
                        $idComment
                    );
                    $statementInsertAcceptedChoice->bindValue(
                        "feedback",
                        $feedbackResult
                    );
                    $statementInsertAcceptedChoice->execute();
                    return [
                        "status" => 1,
                         "id_comment" => $resultIsAccepted["id"],
                          "id_user" => $resultIsAccepted["idUser"], 
                          "id_article" => $resultIsAccepted["idArticle"], 
                          "content" => $resultIsAccepted["content"], 
                          "dateCreation" => $resultIsAccepted["date_of_publication"],
                           "feedback" => $feedback
                        ];
                }
                break;

            case str_contains($valueOfValidation, "Rejeter"):
                $statementRejected = $dbConnect->prepare(
                    "SELECT id,idUser,idArticle 
                    FROM comment 
                    WHERE id = :idComment"
                );
                $statementRejected->bindParam("idComment", $idComment);
                $statementRejected->execute();
                $resultIsRejected = $statementRejected->fetch();
                if ($resultIsRejected) {
                    $feedbackResult = !empty($feedback) ? $feedback : null;
                    $statementInsertRejectedChoice = $dbConnect->prepare(
                        "UPDATE comment 
                        SET status =  :status , feedbackAdministrator = :feedback 
                        WHERE id = :idComment"
                    );
                    $statementInsertRejectedChoice->bindValue(
                        "status", 
                        false
                    );
                    $statementInsertRejectedChoice->bindValue(
                        "idComment", 
                        $idComment
                    );
                    $statementInsertRejectedChoice->bindValue(
                        "feedback", 
                        $feedbackResult
                    );
                    $statementInsertRejectedChoice->execute();
                    return [
                        "status" => 0,
                         "id_comment" => $resultIsRejected["id"],
                         "id_user" => $resultIsRejected["idUser"],
                         "id_article" => $resultIsRejected["idArticle"],
                         "feedback" => $feedback
                    ];
                }
                break;
            }
        }
    }

    /**
     * Summary of deleteComment
     *
     * @param array  $validation 
     * @param array  $session 
     * @param string $idInCookie 
     * 
     * @return void
     */
    public function deleteComment(
        array $validation, 
        array $session, 
        string $idInCookie
    ): void {
        $dbConnect = $this->connector->connect();
        if ($session["session_id"] == $idInCookie) {
            $statementToDeleteComment = $dbConnect->prepare(
                "DELETE FROM comment 
                WHERE id = :idComment"
            );
            $statementToDeleteComment->bindParam(
                "idComment", 
                $validation["id_comment"]
            );
            $statementToDeleteComment->execute();
        }
    }
}

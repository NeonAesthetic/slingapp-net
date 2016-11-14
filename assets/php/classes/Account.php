<?php
/**
 * Accounts Class
 * Created by PhpStorm.
 * User: Isaac, Tristan
 * Date: 11/6/2016
 * Time: 9:21 AM
 */

//add upgrade account from temp function
set_include_path(realpath($_SERVER["DOCUMENT_ROOT"]) . "/assets/php/");
require_once "classes/Database.php";
require_once "interfaces/DatabaseObject.php";

/**
 * This Class handles all Accounts and linked participants in the database.
 * This class will create a new account for  user that does not already have
 * one based on the status of the login token. The account will be used in order
 * to maintain a single participant to any room a single account is participating
 * in. The account class can have at most one participant per session.
 *
 * This class uses SQL statements in order to locate data pertaining to any current
 * accounts in the database and any participants linked to that account and any room
 * that the single participant and account are participating in.
 * */

//password should be between 6 to 30 characters
$minLength = 6;
$maxLength = 30;

class Account extends DatabaseObject
{
    /**
     * @return null
     */
    private $_fName;
    private $_lName;
    private $_email;
    private $_token;
    private $_tokenGen;
    private $_lastLogin;
    private $_joinDate;
    private $_accountID;
    private $_roomID;
    private $_screenName;
    private $_participantID;

    /**
     * This Constructor is used to create a new account based on data that is retrieved from the user at
     * the point of creation. This will include
     * First Name
     * Last Name
     * Email
     * Password
     * The Class will then retrieve/generate
     * AccountID
     * Password Hash
     * Token
     * Token Gen
     * Last Login
     * Join Date
     * These elements make up the new account in the database and will persist until removed on command
     * by the Delete Account function.
     */
    public function __construct($accountID, $token, $tokenGen, $email = null, $fName = null, $lName = null,
                                $lastLogin = null, $joinDate = null)
    {
        #echo "AccountID: $accountID, Token: $token, TokenGen: $tokenGen, LastLogin: $lastLogin, Join Date: $joinDate";
        $this->_accountID = $accountID;
        $this->_email = $email;
        $this->_fName = $fName;
        $this->_lName = $lName;
        $this->_token = $token;
        $this->_tokenGen = $tokenGen;
        $this->_lastLogin = $lastLogin;
        $this->_joinDate = $joinDate;
        $this->_roomID = null;
        $this->_screenName = null;
    }

    /**
     * Function CreateAccount
     * @param $email
     * @param $fName
     * @param $lName
     * @param $password
     * @return Account
     * @throws Exception
     * This function is used to create a new account in the database. It will be called when a user
     * is attempting to join a room without already having an account, or when a user opts to register
     * a new account with the Sling Application.
     * This Function executes the SQL DML Statement Insert to add a new account to the database.
     */
    public static function CreateAccount($email = null, $fName = null, $lName = null, $password = null)
    {
        if ($password)
            $tempPassHash = password_hash($password, PASSWORD_BCRYPT);
        else
            $tempPassHash = null;

        $token = md5(uniqid(mt_rand(), true));
        $currentDate = gmdate("Y-m-d H:i:s");

        $sql = "INSERT INTO Accounts 
                (Email, FirstName, LastName, PasswordHash, LoginToken, TokenGenTime, LastLogin, JoinDate)  
                VALUES(:email, :fName, :lName, :passHash, :logTok, :tokGen, :lastLog, :joinDate)";

        $statement = Database::connect()->prepare($sql);

        if (!$statement->execute([
            ':email' => $email,
            ':fName' => $fName,
            ':lName' => $lName,
            ':passHash' => $tempPassHash,
            ':logTok' => $token,
            ':tokGen' => $currentDate,
            ':lastLog' => $currentDate,
            ':joinDate' => $currentDate
        ])
        ) {
            var_dump(Database::connect()->errorInfo());
            throw new Exception("Could not create account");
        }

        $accountID = Database::connect()->lastInsertId();

        return new Account($accountID, $token, $currentDate, $email, $fName, $lName,
            $currentDate, $currentDate);
    }

    /**
     * Function Login
     * @param $token_email
     * @param null $password
     * @return Account|false
     * This function facilitates the data lookup of a user who is attempting to log into the Sling Application.
     * If the user has provided a password the function will return the account data through an SQL query based
     * on the stored password.
     * If the user has not provided a password, then the system will return a new account provided a login token.
     * If the username or password do not match, the system will return false
     */
    public static function Login($token_email, $password = null)        //add validity checks
    {
//        echo "Password:: $password";
        $retval = null;
        $currentDate = gmdate("Y-m-d H:i:s");
        if ($password) {
            $sql = "SELECT *
                FROM Accounts
                WHERE Email = :email";
            $statement = Database::connect()->prepare($sql);
            $statement->execute(array(':email' => $token_email));
            $result = $statement->fetch(PDO::FETCH_ASSOC);

            if ($result && password_verify($password, $result['PasswordHash']))    //adds quiet a bit of overhead
            {
                $retval = new Account($result['AccountID'], $result['LoginToken'], $result['TokenGenTime'],
                    $result['Email'], $result['FirstName'], $result['LastName'], $currentDate, $result['JoinDate']);

                $sql = "UPDATE Accounts
                SET LastLogin = :lastLog
                WHERE Email = :email";
                if (!Database::connect()->prepare($sql)->execute(array(':lastLog' => $currentDate, ':email' => $token_email)))
                    $retval = null;
            }

        } else {      //no password provided, lookup based on token
            $sql = "SELECT *
                FROM Accounts
                WHERE LoginToken = :logtok";

            $statement = Database::connect()->prepare($sql);
            $statement->execute(array(':logtok' => $token_email));
            $result = $statement->fetch(PDO::FETCH_ASSOC);

//            echo "AccountID After Login:::: ";
//            var_dump($result['AccountID']);
            if ($result) {
                $retval = new Account($result['AccountID'], $result['LoginToken'], $result['TokenGenTime'],
                    $result['Email'], $result['FirstName'], $result['LastName'], $result['LastLogin'], $result['JoinDate']);

                $sql = "UPDATE Accounts
                SET LastLogin = :lastLog
                WHERE LoginToken = :token";
                //if account last login doesn't update don't return account
                if (!Database::connect()->prepare($sql)->execute(array(':lastLog' => $currentDate, ':token' => $token_email)))
                    $retval = null;

            } else {
                $retval = false;
            }
        }

        return $retval;
    }

    public static function LoginThroughID($AccountID)
    {
        $retval = null;

        $currentDate = gmdate("Y-m-d H:i:s");

        $sql = "SELECT *
            FROM Accounts
            WHERE AccountID = :accountID";
        $statement = Database::connect()->prepare($sql);
        $retval = $statement->execute(array(':accountID' => $AccountID));
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($retval) {
            $retval = new Account($result['AccountID'], $result['LoginToken'], $result['TokenGenTime'],
                $result['Email'], $result['FirstName'], $result['LastName'], $currentDate, $result['JoinDate']);

            $sql = "UPDATE Accounts
                SET LastLogin = :lastLog
                WHERE AccountID = :accountID";
            //if account last login doesn't update don't return account
            if (!Database::connect()->prepare($sql)->execute(array(':lastLog' => $currentDate, ':accountID' => $AccountID)))
                $retval = null;
        }
        return $retval;
    }

//    public static function createTempAccount()
//    {
//        $retval = null;
//        $token = md5(uniqid(mt_rand(), true));
//        $currentDate = gmdate("Y-m-d H:i:s");
//
//        $sql = "INSERT INTO Accounts
//                (Email, FirstName, LastName, PasswordHash, LoginToken, TokenGenTime, LastLogin, JoinDate)
//                VALUES(:email, :fName, :lName, :passHash, :logTok, :tokGen, :lastLog, :joinDate)";
//
//        $statement = Database::connect()->prepare($sql);
//
//        if ($statement->execute([
//            ':email' => null,
//            ':fName' => null,
//            ':lName' => null,
//            ':passHash' => null,
//            ':logTok' => $token,
//            ':tokGen' => $currentDate,
//            ':lastLog' => $currentDate,
//            ':joinDate' => $currentDate,
//        ])
//        ) {
//            $accountID = Database::connect()->lastInsertId();
//
//            $sql = "SELECT *
//                    FROM Accounts AS a
//                      JOIN Participants AS p
//                        ON a.AccountID = p.AccountID
//                    WHERE Email IS NULL";
//
//            Database::connect()->prepare($sql)->execute();
//
//            $account = new Account($accountID, $token, $currentDate);
//            $retval = $account;
//        }
//        return $retval;
//    }

    /**
     * Function Delete
     * @return boolean
     * This function will remove an account from the database.
     * This function uses an SQL statement in order to find an
     * existing account based on an account's token. The returned
     * accountID will then be used to remove any associated
     * participants and then will remove the account itself.
     * This function can be used to either have an account
     * explicity deleted by the user or automatically for
     * temporary accounts
     */
    public function delete()
    {
        $retval = false;

        $sql = "SELECT AccountID                                                                    
                    FROM Accounts
                    WHERE LoginToken = :logtok";
        $statement = Database::connect()->prepare($sql);

        if ($statement->execute(array(':logtok' => $this->_token))) {
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

            $this->_accountID = $result[0]["AccountID"];

            //if a participant exists, delete it (check for roomID)
            if ($this->_roomID) {
                $sql = "DELETE FROM Particpants
                    WHERE AccountID = $this->_accountID";
                $statement = Database::connect()->prepare($sql);
                if ($retval = $statement->execute()) {
                    $this->_roomID = null;
                    $this->_screenName = null;
                }

            }//if participant was deleted successfully or if it didn't exist, delete account
            if (!$this->_roomID) {
                $sql = "DELETE FROM Accounts
                        WHERE AccountID = $this->_accountID";
                $statement = Database::connect()->prepare($sql);
                $retval = ($statement->execute()) ? true : false;
            }
        }
        return $retval;
    }

    /**
     * Function Update
     * This function will trigger whenever a setter is used or a user attempts to join a room,
     * it will attempt to insert the account
     * data and the participant data to correlate with the new room and participant status.
     */
    //NEEDED:   Update Account status based on room to join and allow linked participant to join room
    //NEEDED:   Test that allows room to be created-> then account-> then update to move account and part. to room
    public function update()
    {
        if ($this->_roomID) {            //account has participant
            $sql = "UPDATE Accounts AS a 
                      JOIN Participants AS p
                        ON a.AccountID = p.AccountID
                    SET Email = :email,
                    FirstName = :fName,
                    LastName = :lName,
                    LoginToken = :logTok,
                    TokenGenTime = :tokGen,
                    LastLogin = :lastLog,
                    JoinDate = :joinDate,
                    RoomID = :roomID,
                    ScreenName = :screenName
                WHERE a.AccountID = :accountID";

            $statement = Database::connect()->prepare($sql);
            $statement->execute(array(':email' => $this->_email,
                ':fName' => $this->_fName,
                ':lName' => $this->_lName,
                ':logTok' => $this->_token,
                ':tokGen' => $this->_tokenGen,
                ':lastLog' => $this->_lastLogin,
                ':joinDate' => $this->_joinDate,
                ':accountID' => $this->_accountID,
                ':roomID' => $this->_roomID,
                ':screenName' => $this->_screenName));
        } else {    //account doesn't have a participant
            $sql = "UPDATE Accounts
                SET Email = :email,
                    FirstName = :fName,
                    LastName = :lName,
                    LoginToken = :logTok,
                    TokenGenTime = :tokGen,
                    LastLogin = :lastLog,
                    JoinDate = :joinDate
                WHERE AccountID = :accountID";

            $statement = Database::connect()->prepare($sql);
            $statement->execute(array(':email' => $this->_email,
                ':fName' => $this->_fName,
                ':lName' => $this->_lName,
//                ':passHash' => $this->_passHash,
                ':logTok' => $this->_token,
                ':tokGen' => $this->_tokenGen,
                ':lastLog' => $this->_lastLogin,
                ':joinDate' => $this->_joinDate,
                ':accountID' => $this->_accountID));
        }
    }

    /**
     * Function updateAfterSetter
     * This function will trigger whenever a setter is used and will attempt to insert the
     * updated account/participant data. Data must pass validity checks before this function
     * is called. Validity checks are in the __set() function
     */
    public function addParticipant($roomID, $screenName)
    {
//        echo "ROOM ID AFTER: ", $roomID;
        //Do select statement and pull account id after account created.
        $sql = "INSERT INTO Participants
                (AccountID, RoomID, ScreenName)
                VALUES (:accountID, :roomID, :screenName)";
        $statement = Database::connect()->prepare($sql);

//        echo "AccountID::::: $this->_accountID";
        if ($statement->execute(array(':accountID' => $this->_accountID, ':roomID' => $roomID, ':screenName' => $screenName))
        ) {
            $this->_participantID = Database::connect()->lastInsertId();
            $this->_roomID = $roomID;
            $this->_screenName = $screenName;
        }
    }

    public function updatePass($pass)
    {
        if (strlen($pass) >= $GLOBALS['minLength'] && strlen($pass) <= $GLOBALS['maxLength']) {
            $hashedPass = password_hash($pass, PASSWORD_BCRYPT);

            $sql = "UPDATE Accounts
                SET PasswordHash = :passHash
                WHERE AccountID = :accountID";
            if (Database::connect()->prepare($sql)->execute(array(':passHash' => $hashedPass, ':accountID' => $this->_accountID))) {
                DatabaseObject::Log(__FILE__, "Updated Account",
                    "Account: $this->_accountID \n updated password");
            }

        } else
            throw new Exception("Password must be between 6 - 30 characters");
        return;
    }

    private function isNameValid($name)
    {
        $nameExp = "/^[^<,\"(){}@*$%?=>:|;#]*$/i";
        return preg_match($nameExp, $name) ? 1 : 0;
    }

    /**
     * @return mixed
     */
    public function getAccountID()
    {
        return $this->_accountID;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * @return mixed
     */
    public function getParticipantID()
    {
        return $this->_participantID;
    }


    /**
     * @return mixed
     */
    public function getRoomID()
    {
        return $this->_roomID;
    }

    /**
     * @return string[]
     */
    public function getName()
    {
        return ["First" => $this->_fName, "Last" => $this->_lName];
    }

    /**
     * Function getJSON
     * @return string | array
     * This function allows the Accounts type to be encoded.
     */
    public function getJSON($as_array = false)
    {
        $json = [];
        $json['Type'] = "Account";
        $json['Email'] = $this->_email;
        $json["FirstName"] = $this->_fName;
        $json["LastName"] = $this->_lName;
        $json["LoginToken"] = $this->_token;
        $json['ID'] = $this->_accountID;
        $json['ScreenName'] = $this->_screenName;
        if ($as_array)
            return $json;
        return json_encode($json);
    }

    /**
     * @return mixed
     */
    public function getScreenName()
    {
        return $this->_screenName;
    }

    /**
     * Function getEmail
     * @return mixed
     * This function allows the Current Account Email to be returned.
     */
    public function getEmail()
    {
        return $this->_email;
    }

    function __set($name, $value)
    {
        switch (strtolower($name)) {
            case "_email":
                if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $temp = $this->_email;
                    $this->_email = $value;
                    DatabaseObject::Log(__FILE__, "Updated Account",
                        "Account: $this->_accountID \n Updated Email From: $temp to: $value");
                } else
                    throw new Exception("Email is not valid, please try again.");


                break;
            case "_fname":
                if ($this->isNameValid($value)) {
                    $temp = $this->_fName;
                    $this->_fName = $value;
                    DatabaseObject::Log(__FILE__, "Updated Account",
                        "Account: $this->_accountID \n Updated First Name From: $temp to: $value");
                } else
                    throw new Exception("First name is not valid, please try again.");


                break;
            case "_lname":
                if ($this->isNameValid($value)) {
                    $temp = $this->_lName;
                    $this->_lName = $value;
                    DatabaseObject::Log(__FILE__, "Updated Account",
                        "Account: $this->_accountID \n Updated Last Name From: $temp to: $value");
                } else
                    throw new Exception("Last name is not valid, please try again.");

                break;
            case "_token":
                $this->_token = md5(uniqid(mt_rand(), true));
                $this->_tokenGen = gmdate('Y-m-d H:i:s');
                DatabaseObject::Log(__FILE__, "Updated Account",
                    "Account: $this->_accountID \n Updated token");
                break;
            case "_roomid":
                $temp = $this->_roomID;
                $this->_roomID = $value;
                DatabaseObject::Log(__FILE__, "Updated Account",
                    "Account: $this->_accountID \n Updated roomID from: $temp to: $value");
                break;
            case "_screenname":             //add validation check
                $temp = $this->_screenName;
                $this->_screenName = $value;
                DatabaseObject::Log(__FILE__, "Updated Account",
                    "Account: $this->_accountID \n Updated screenname from: $temp to: $value");
                break;
            case "_participantid":
                echo "NAME::: ", $name;
                $temp = $this->_screenName;
                $this->_participantID = $value;
                DatabaseObject::Log(__FILE__, "Updated Account",
                    "Account: $this->_accountID \n Updated participantID from: $temp to: $value");
                break;
            default:
                DatabaseObject::Log(__FILE__, "Updated Account",
                    "Account: $this->_accountID \n set method using: $name wasn't valid");
        }

        $this->update();

        return $value;
    }
}
<?php
/**
 * Accounts Class
 * Created by PhpStorm.
 * User: Isaac, Tristan
 * Date: 11/6/2016
 * Time: 9:21 AM
 */

//add upgrade account from temp function
require_once "Database.php";
require_once "interfaces/DatabaseObject.php";

/**
 * This Class handles all Accounts .
 * This class will create a new account for  user that does not already have
 * one based on the status of the login token..
 * This class uses SQL statements in order to locate data pertaining to any current
 * accounts in the database.
 * */

//CONSTANTS
$MINLENGTH = 6;
$MAXLENGTH = 30;

class Account extends DatabaseObject
{
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
    private $_active;

    /**
     * Account-Tests constructor.
     * @param $accountID
     * @param $token
     * @param $tokenGen
     * @param null $email
     * @param null $fName
     * @param null $lName
     * @param null $lastLogin
     * @param null $joinDate
     * @param null $roomID
     * @param null $screenName
     * @param boolean $active
     * Function Constructor is used to create a new account based on data that is retrieved from the user at
     * the point of creation.
     * These elements make up the new account in the database and will persist until removed on command
     * by the Delete Account-Tests function.
     */
    public function __construct($accountID, $token, $tokenGen = null, $email = null, $fName = null, $lName = null,
                                $lastLogin = null, $joinDate = null, $roomID = null, $screenName = null, $active = true)
    {

//        error_log("INSTANTIATION ACCID: " . $accountID);
        $this->_accountID = $accountID;
        $this->_email = $email;
        $this->_fName = $fName;
        $this->_lName = $lName;
        $this->_token = $token;
        $this->_tokenGen = $tokenGen;
        $this->_lastLogin = $lastLogin;
        $this->_joinDate = $joinDate;
        $this->_roomID = $roomID;
        $this->_screenName = $screenName;
        $this->_active = $active;
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
     * is attempting to join a rooms without already having an account, or when a user opts to register
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
        $accountID = Database::getFlakeID();

        $sql = "INSERT INTO Accounts 
                (AccountID, Email, FirstName, LastName, PasswordHash, LoginToken, TokenGenTime, LastLogin, JoinDate)  
                VALUES(:accid, :email, :fName, :lName, :passHash, :logTok, :tokGen, :lastLog, :joinDate)";

        $statement = Database::connect()->prepare($sql);

        if (!$statement->execute([
            ":accid" => $accountID,
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
    public static function Login($token_email, $password = null)
    {
        $retval = null;
        $currentDate = gmdate("Y-m-d H:i:s");
        if ($password) {
            $sql = "SELECT *
                FROM Accounts AS a 
                  LEFT JOIN RoomAccount AS ra
                    ON a.AccountID = ra.AccountID
                  LEFT JOIN Rooms AS r
                    ON ra.RoomID = r.RoomID
                WHERE Email = :email";

            $statement = Database::connect()->prepare($sql);
            $statement->execute(array(':email' => $token_email));
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            
            if ($result && password_verify($password, $result['PasswordHash'])) {
                if($result['RoomID'])   //if participating in room
                    $retval = new Account($result['AccountID'], $result['LoginToken'], $result['TokenGenTime'],
                        $result['Email'], $result['FirstName'], $result['LastName'], $currentDate, $result['JoinDate'],
                        $result['RoomID'], $result['ScreenName'], $result['Active']);
                else                    //if not participating in room
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
                    FROM Accounts a 
                      WHERE a.LoginToken = :logtok";
            $statement = Database::connect()->prepare($sql);
            $statement->execute(array(':logtok' => $token_email));
            $result = $statement->fetch(PDO::FETCH_ASSOC);
//            var_dump($result);

            if ($result) {
                error_log("[". __LINE__ ."]" . $result["AccountID"]);
//                if($result['RoomID'] != null) { //if participating in room
//                    echo "RoomID:: ", $result['RoomID'];
//                    $retval = new Account($result['AccountID'], $result['LoginToken'], $result['TokenGenTime'],
//                        $result['Email'], $result['FirstName'], $result['LastName'], $currentDate, $result['JoinDate'],
//                        $result['RoomID'], $result['ScreenName'], $result['Active']);
//                }
//                else {  //if not participating in room
                    $retval = new Account($result['AccountID'], $result['LoginToken'], $result['TokenGenTime'],
                        $result['Email'], $result['FirstName'], $result['LastName'], $currentDate, $result['JoinDate']);
//                }
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
//    /**
//     * Function LoginThroughID
//     * @param $AccountID
//     * @return Account|bool|null
//     * This function allows a user to login based on the accountID
//     * that is provided this function returns the account based on
//     * the ID lookup that it performs in the Accounts table.
//     */
//    public static function LoginThroughID($AccountID)
//    {
//        $retval = null;
//        $currentDate = gmdate("Y-m-d H:i:s");
//        $sql = "SELECT *
//            FROM Accounts
//            WHERE AccountID = :accountID";
//        $statement = Database::connect()->prepare($sql);
//        $retval = $statement->execute(array(':accountID' => $AccountID));
//        $result = $statement->fetch(PDO::FETCH_ASSOC);
//
//        if ($retval) {
//            $retval = new Account($result['AccountID'], $result['LoginToken'], $result['TokenGenTime'],
//                $result['Email'], $result['FirstName'], $result['LastName'], $currentDate, $result['JoinDate'],
//                $result['RoomID'], $result['ScreenName'], $result['Active']);
//
//            $sql = "UPDATE Accounts
//                SET LastLogin = :lastLog
//                WHERE AccountID = :accountID";
//            //if account last login doesn't update don't return account
//            if (!Database::connect()->prepare($sql)->execute(array(':lastLog' => $currentDate, ':accountID' => $AccountID))) {
//                $retval = null;
//            }
//        }
//        return $retval;
//    }

//
//    public function setRoomID($rid){
//        $this->_roomID = $rid;
//    }

//    public function getParticipantInfo()
//    {
//        $sql = "SELECT RoomID, ScreenName
//                FROM Accounts
//                WHERE AccountID = :accid";
//        $stmt = Database::connect()->prepare($sql);
//        $stmt->execute([
//            ":accid" => $this->_accountID
//        ]);
//        $results = $stmt->fetch(PDO::FETCH_ASSOC);
//        if ($results) {
//            $this->_roomID = $results["RoomID"];
//            $this->_participantID = $results["ParticipantID"];
//            $this->_screenName = $results["ScreenName"];
//        }
//    }

//    public function updateParticipant()
//    {
//        $sql = "INSERT INTO Accounts
//                (RoomID, AccountID, ScreenName)
//                VALUES(:rmid, :accid, :sn)
//                ON DUPLICATE KEY
//                UPDATE RoomID = :rmid, ScreenName = :sn, AccountID = :accid;";
//        Database::connect()->prepare($sql)->execute([
//            ":rmid" => $this->_roomID,
//            ":sn" => $this->_screenName,
//            ":accid" => $this->_accountID
//        ]);
//    }

    /**
     * Function Delete
     * @return boolean
     * This function will remove an account from the database.
     * This function uses an SQL statement in order to find an
     * existing account based on an account's token.
     * This function can be used to either have an account
     * explicity deleted by the user or automatically for
     * temporary accounts
     */
    public function delete()
    {
        $sql = "DELETE FROM Accounts
                WHERE AccountID = $this->_accountID";
        $statement = Database::connect()->prepare($sql);
        return ($statement->execute()) ? true : false;

    }
    /**
     * Function Update
     * This function will trigger whenever a setter is used or a user attempts to join a rooms,
     * it will attempt to insert the account
     * data to correlate with the new rooms status.
     */
    //NEEDED:   Update Account-Tests status based on rooms to join
    //NEEDED:   Test that allows rooms to be created-> then account-> then update to move account and part. to rooms
    public function update()
    {
        $sql = "UPDATE Accounts
                SET Email = :email,
                FirstName = :fName,
                LastName = :lName,
                LoginToken = :logTok,
                TokenGenTime = :tokGen,
                LastLogin = :lastLog,
                JoinDate = :joinDate,
                ScreenName = :screenName,
                Active = :active  
                WHERE AccountID = :accountID";

        $statement = Database::connect()->prepare($sql);
        if (!$statement->execute(array(
            ':email' => $this->_email,
            ':fName' => $this->_fName,
            ':lName' => $this->_lName,
            ':logTok' => $this->_token,
            ':tokGen' => $this->_tokenGen,
            ':lastLog' => $this->_lastLogin,
            ':joinDate' => $this->_joinDate,
            ':accountID' => $this->_accountID,
//            ':roomID' => $this->_roomID,
            ':screenName' => $this->_screenName,
            ':active' => $this->_active))
        )
            error_log("ACCOUNT UPDATE FAILURE: " . $statement->errorInfo()[2]);
         else
            error_log("UPDATE WORKED");

    }

    /**
     * Function Update Password
     * @param $pass
     * @throws Exception
     * This function allows a user to update the password stored in the database
     * based on an existing account ID. This will allow user validity checks to
     * be run elsewhere, this function will simply handle setting the new value.
     */
    public function updatePass($pass)
    {
        if (strlen($pass) >= $GLOBALS['MINLENGTH'] && strlen($pass) <= $GLOBALS['MAXLENGTH']) {
            $hashedPass = password_hash($pass, PASSWORD_BCRYPT);
            $sql = "UPDATE Accounts
                SET PasswordHash = :passHash
                WHERE AccountID = :accountID";
            if (Database::connect()->prepare($sql)->execute(array(':passHash' => $hashedPass, ':accountID' => $this->_accountID))) {
                DatabaseObject::Log(__FILE__, "Updated Account-Tests",
                    "Account-Tests: $this->_accountID \n updated password");
            }
        } else
            throw new Exception("Password must be between 6 - 30 characters");
        return;
    }

    /**
     * @param $name
     * @return int
     */
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
     * @return bool
     */
    public function getActive()
    {
        return $this->_active;
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
        $json['Type'] = "Account-Tests";
        $json['Email'] = $this->_email;
        $json["FirstName"] = $this->_fName;
        $json["LastName"] = $this->_lName;
        $json["LoginToken"] = $this->_token;
        $json['ID'] = $this->_accountID;
        $json['ScreenName'] = $this->_screenName;
        $json['RoomID'] = $this->_roomID;
        $json['Active'] = $this->_active;
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

    // use magic setter
//    public function setScreenName($sn){
//        $this->_screenName = $sn;
//    }
//    /**
//     * Function getEmail
//     * @return mixed
//     * This function allows the Current Account-Tests Email to be returned.
//     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * Function Set
     * @param $name
     * @param $value
     * @return mixed
     * @throws Exception
     * This function is a switch case that uses the different names it is passed in order to set different
     * parameters throughout the database. The value passed is used in order to set a new value for the passed
     * name, then the update function is called to finalize the changes.
     */
    function __set($name, $value)
    {
        switch (strtolower($name)) {
            case "_email":
                if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $temp = $this->_email;
                    $this->_email = $value;
                    DatabaseObject::Log(__FILE__, "Updated Account-Tests",
                        "Account-Tests: $this->_accountID \n Updated Email From: $temp to: $value");
                } else
                    throw new Exception("Email is not valid, please try again.");


                break;
            case "_fname":
                if ($this->isNameValid($value)) {
                    $temp = $this->_fName;
                    $this->_fName = $value;
                    DatabaseObject::Log(__FILE__, "Updated Account-Tests",
                        "Account-Tests: $this->_accountID \n Updated First Name From: $temp to: $value");
                } else
                    throw new Exception("First name is not valid, please try again.");


                break;
            case "_lname":
                if ($this->isNameValid($value)) {
                    $temp = $this->_lName;
                    $this->_lName = $value;
                    DatabaseObject::Log(__FILE__, "Updated Account-Tests",
                        "Account-Tests: $this->_accountID \n Updated Last Name From: $temp to: $value");
                } else
                    throw new Exception("Last name is not valid, please try again.");

                break;
            case "_token":
                $this->_token = md5(uniqid(mt_rand(), true));
                $this->_tokenGen = gmdate('Y-m-d H:i:s');
                DatabaseObject::Log(__FILE__, "Updated Account-Tests",
                    "Account-Tests: $this->_accountID \n Updated token");
                break;
            case "_roomid":
                $temp = $this->_roomID;
                $this->_roomID = $value;
                DatabaseObject::Log(__FILE__, "Updated Account-Tests",
                    "Account-Tests: $this->_accountID \n Updated roomID from: $temp to: $value");
                break;
            case "_screenname":             //add validation check
                $temp = $this->_screenName;
                $this->_screenName = $value;
                DatabaseObject::Log(__FILE__, "Updated Account-Tests",
                    "Account-Tests: $this->_accountID \n Updated screenname from: $temp to: $value");
                break;
            case "_active":
                $temp = $this->_active;
                $this->_active = $value;
                DatabaseObject::Log(__FILE__, "Updated Account-Tests",
                    "Account-Tests: $this->_accountID \n Updated active from: $temp to: $value");
                break;
            default:
                DatabaseObject::Log(__FILE__, "Updated Account-Tests",
                    "Account-Tests: $this->_accountID \n set method using: $name wasn't valid");
        }

        $this->update();
        return $value;
    }

    public function __toString()
    {
        return (string)$this->_accountID;
    }
}
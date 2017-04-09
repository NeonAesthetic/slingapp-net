<?php
/**
 * Accounts Class
 * Created by PhpStorm.
 * User: Isaac, Tristan
 * Date: 11/6/2016
 * Time: 9:21 AM
 */

require_once "classes/Database.php";
require_once "interfaces/DatabaseObject.php";
//require_once "classes/logging/Logger.php";


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
    private $_passhash;

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
                                $lastLogin = null, $joinDate = null, $screenName = null, $active = true)
    {
        $this->_accountID = $accountID;
        $this->_email = $email;
        $this->_fName = $fName;
        $this->_lName = $lName;
        $this->_token = $token;
        $this->_tokenGen = $tokenGen;
        $this->_lastLogin = $lastLogin;
        $this->_joinDate = $joinDate;
//        $this->_roomID = $roomID;
        $this->_screenName = $screenName;
        $this->_active = $active;
    }

    /**
     * Function CreateAccount
     * @param $email
     * @param $fName
     * @param $lName
     * @param $password
     * @param $xToken
     * @return Account
     * @throws Exception
     * This function is used to create a new account in the database. It will be called when a user
     * is attempting to join a rooms without already having an account, or when a user opts to register
     * a new account with the Sling Application.
     * This Function executes the SQL DML Statement Insert to add a new account to the database.
     */
    public static function CreateAccount($email = null, $fName = null, $lName = null, $password = null, $xToken = null)
    {
        $tempPassHash = ($password) ? password_hash($password, PASSWORD_BCRYPT) : null;

        if ($xToken) {      //xToken = preexisting token
            $token = $xToken;
            $token[0] = '1';
        } else {
            $token = md5(uniqid(mt_rand(), true));
            $token = ($email) ? "1" . $token : "0" . $token;    // 1 is permanent account, 0 is temp
        }

        $currentDate = gmdate("Y-m-d H:i:s");
        $accountID = Database::getFlakeID();
        $screenName = "Anonymous " . Database::getRandomAnimal();

//        $sql = "INSERT INTO Accounts
//                (AccountID, Email, FirstName, LastName, PasswordHash, LoginToken, TokenGenTime, LastLogin, JoinDate, ScreenName)
//                VALUES(:accid, :email, :fName, :lName, :passHash, :logTok, :tokGen, :lastLog, :joinDate, :sn)";

//        $statement = Database::connect()->prepare($sql);

//        if (!$statement->execute([
//            ":accid" => $accountID,
//            ':email' => $email,
//            ':fName' => $fName,
//            ':lName' => $lName,
//            ':passHash' => $tempPassHash,
//            ':logTok' => $token,
//            ':tokGen' => $currentDate,
//            ':lastLog' => $currentDate,
//            ':joinDate' => $currentDate,
//            ':sn' => $screenName])
//        ) {
////            var_dump(Database::connect()->errorInfo());
//            $retval = json_encode(['error' => "Database could not be reached"]);
//        } else{}
        $retval = new Account($accountID, $token, $currentDate, $email, $fName, $lName, $currentDate, $currentDate, $screenName, 1);
        $retval->_passhash = $tempPassHash;
            $retval->update();
        return $retval;
    }

    private function generateToken(){
        $token = md5(uniqid(mt_rand(), true));
        $token = ($this->_email) ? "1" . $token : "0" . $token;    // 1 is permanent account, 0 is temp
        return $token;
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

    public static function CheckDatabase($email)
    {
        $sql = "SELECT *
                    FROM Accounts AS a 
                    WHERE Email = :email";

        $statement = Database::connect()->prepare($sql);
        $statement->execute([':email' => $email]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return ($result) ? true:false;
    }
    public static function Login($token_email, $password = null)
    {
        $retval = null;
        $currentDate = gmdate("Y-m-d H:i:s");

        try {
            if ($password) {
                $sql = "SELECT *
                    FROM Accounts AS a 
                      LEFT JOIN RoomAccount AS ra
                        ON a.AccountID = ra.AccountID
                      LEFT JOIN Rooms AS r
                        ON ra.RoomID = r.RoomID
                    WHERE Email = :email";

                $statement = Database::connect()->prepare($sql);
                $statement->execute([':email' => $token_email]);
                $result = $statement->fetch(PDO::FETCH_ASSOC);

                if ($result) {
//                    if ($result['AccountActive']) {
                        if (password_verify($password, $result['PasswordHash'])) {

                            if ($result['RoomID'])   //if participating in room
                                $retval = new Account($result['AccountID'], $result['LoginToken'], $result['TokenGenTime'],
                                    $result['Email'], $result['FirstName'], $result['LastName'], $currentDate, $result['JoinDate'],
                                    $result['ScreenName'], $result['AccountActive']);
                            else                    //if not participating in room
                                $retval = new Account($result['AccountID'], $result['LoginToken'], $result['TokenGenTime'],
                                    $result['Email'], $result['FirstName'], $result['LastName'], $currentDate, $result['JoinDate']);

                            $sql = "UPDATE Accounts
                            SET LastLogin = :lastLog    
                            WHERE Email = :email";

                            if (!Database::connect()->prepare($sql)->execute([':lastLog' => $currentDate, ':email' => $token_email]))
                                throw new Exception("Database unable to update account's last login information");
                        } else
                            throw new Exception("Password is Incorrect");
//                    } else
//                        throw new Exception("Account is deactivated");
                } else
                    throw new Exception("Incorrect Username or Password");
            } else {      //no password provided, lookup based on token
                $sql = "SELECT *
                    FROM Accounts 
                    WHERE LoginToken = :logtok";
                $statement = Database::connect()->prepare($sql);
                $statement->execute([':logtok' => $token_email]);
                $result = $statement->fetch(PDO::FETCH_ASSOC);

                if ($result) {
                    $retval = new Account($result['AccountID'], $result['LoginToken'], $result['TokenGenTime'],
                        $result['Email'], $result['FirstName'], $result['LastName'], $currentDate, $result['JoinDate'], $result["ScreenName"], 1);

                    $sql = "UPDATE Accounts
                SET LastLogin = :lastLog
                WHERE LoginToken = :token";
                    //if account last login doesn't update don't return account
                    if (!Database::connect()->prepare($sql)->execute([':lastLog' => $currentDate, ':token' => $token_email]))
                        throw new Exception("Database unable to update account's last login information"); //replace with log error instead

                } else
                    throw new Exception("Unable to login using token");   //replace with log error instead
            }
        } catch (Exception $e) {
            $retval = false;
        }
        return $retval;
    }
    /**
     * Function getRoomsUserIsIn
     * @param $accountID
     * @return string
     */
    public function getRoomsUserIsIn()
    {
        $sql = "SELECT r.RoomName, r.RoomID, COUNT(*) as NumUsers FROM RoomAccount AS a 
                JOIN Rooms AS r ON a.RoomID = r.RoomID
                JOIN RoomAccount AS ra ON r.RoomID = ra.RoomID
                WHERE a.AccountID = :accountID 
                GROUP BY r.RoomID";
        $statement = Database::connect()->prepare($sql);
        $statement->execute([':accountID' => $this->_accountID]);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

//        $json = [];
//        $json['Type'] = "RoomData";
//        $json['Rooms']=[];
//
//        foreach ($result as $row) {
//            if ($row["RoomName"] !== null)
//                $json['Rooms'] = $row['RoomName'];
//            if($row["Active"] !== null)
//                $json['Active'] = $row['Active'];
//        }
        return $result;
    }
    //14948841491605656826
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
    public function update()
    {
        $sql = "INSERT INTO Accounts
                (AccountID, Email, FirstName, LastName, PasswordHash, LoginToken, TokenGenTime, LastLogin, JoinDate, ScreenName)                
                VALUES(:accid, :email, :fName, :lName, :passHash, :logTok, :tokGen, :lastLog, :joinDate, :screenName) 
                ON DUPLICATE KEY UPDATE
                Email = :email,
                FirstName = :fName,
                LastName = :lName,
                LoginToken = :logTok,
                TokenGenTime = :tokGen,
                LastLogin = :lastLog,
                JoinDate = :joinDate,
                ScreenName = :screenName";

        $statement = Database::connect()->prepare($sql);
        if (!$statement->execute([
            ':accid' => $this->_accountID,
            ':email' => $this->_email,
            ':fName' => $this->_fName,
            ':lName' => $this->_lName,
            ':logTok' => $this->_token,
            ':tokGen' => $this->_tokenGen,
            ':lastLog' => $this->_lastLogin,
            ':joinDate' => $this->_joinDate,
            ':screenName' => $this->_screenName,
            ':passHash' => $this->_passhash,])
        ) ;
    }

    /**
     * Function Update Password
     * @param $pass
     * @throws Exception
     * @return bool
     * This function allows a user to update the password stored in the database
     * based on an existing account ID. This will allow user validity checks to
     * be run elsewhere, this function will simply handle setting the new value.
     */
    public function updatePass($pass)
    {
        $retval = false;
        if (strlen($pass) >= $GLOBALS['MINLENGTH'] && strlen($pass) <= $GLOBALS['MAXLENGTH']) {
            $hashedPass = password_hash($pass, PASSWORD_BCRYPT);
            $sql = "UPDATE Accounts
                SET PasswordHash = :passHash
                WHERE AccountID = :accountID";
            if (Database::connect()->prepare($sql)->execute([':passHash' => $hashedPass,
                                                             ':accountID' => $this->_accountID]))
                $retval = true;
        } else
            throw new Exception("Password must be between 6 - 30 characters");

        return $retval;
    }

    /**
     * Function Deactivate
     * This function will deactivate the account if requested by the user.
     */
    public function Deactivate()
    {
    }

    /**
     * Function isNameValid
     * @param $name
     * @return int
     */
    private function isNameValid($name)
    {
        $nameExp = "/^[^<,\"(){}@*$%?=>:|;#]*$/i";
        return preg_match($nameExp, $name) ? 1 : 0;
    }

    /**
     * Function getAccountID
     * @return int
     */
    public function getAccountID()
    {
        return $this->_accountID;
    }

    /**
     * Function getActive
     * @return bool
     */
    public function isActive()
    {
        return $this->_active;
    }

    public function isFullAccount(){
        return (bool)$this->_email;
    }

    /**
     * Function getToken
     * @return string
     */
    public function getToken()
    {
        return $this->_token;
    }

    public function regenerateToken(){
        $this->_token = $this->generateToken();
    }

    /**
     * Function getRoomID
     * @return integer
     */
    public function getRoomID()
    {
        return $this->_roomID;
    }

    /**
     * Function getName
     * @return string[string]
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
     * Function getScreenName
     * @return string
     */
    public function getScreenName()
    {
        return $this->_screenName;
    }

    /**
     * @return null
     */
    public function setScreenName($name)
    {
        $this->_screenName = $name;
    }
    
    

    /**
     * Function getEmail
     * @return mixed
     * This function allows the Current Account-Tests Email to be returned.
     */
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
                    $this->_email = $value;
                } else
                    throw new Exception("Email is not valid, please try again.");


                break;
            case "_fname":
                if ($this->isNameValid($value)) {
                    $this->_fName = $value;
                } else
                    throw new Exception("First name is not valid, please try again.");


                break;
            case "_lname":
                if ($this->isNameValid($value)) {
                    $this->_lName = $value;
                } else
                    throw new Exception("Last name is not valid, please try again.");

                break;
            case "_token":
                $this->_token = md5(uniqid(mt_rand(), true));
                $this->_tokenGen = gmdate('Y-m-d H:i:s');
                break;
            case "_roomid":
                $this->_roomID = $value;
                break;
            case "_screenname":             //add validation check
                $this->_screenName = $value;
                break;
            case "_active":
                $this->_active = $value;
                break;
            default:
        }

        $this->update();
        return $value;
    }

    /**
     * Function __toString
     * @return string
     * This function returns the account id if instance is treated as a string
     */
    public function __toString()
    {
        return (string)$this->_accountID;
    }
}
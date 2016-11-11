<?php
/**
 * Accounts Class
 * Created by PhpStorm.
 * User: Isaac, Tristan
 * Date: 11/6/2016
 * Time: 9:21 AM
 */
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
class Account extends DatabaseObject
{
    /**
     * @return string[]
     */
    public function getName()
    {
        return ["First"=>$this->_fName, "Last"=>$this->_lName];
    }

    /**
     * @return null
     */
    private $_fName;
    private $_lName;
    private $_email;
    private $_passHash;
    private $_token;
    private $_tokenGen;
    private $_lastLogin;
    private $_joinDate;
    private $_accountID;
    private $_roomID;
    private $_screenName;

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
    public function __construct($accountID, $token, $_tokenGen, $email = null, $fName = null, $lName = null, $passHash = null
        , $lastLogin = null , $joinDate = null)
    {
        $this->_accountID = $accountID;
        $this->_email = $email;
        $this->_fName = $fName;
        $this->_lName = $lName;
        $this->_passHash = $passHash;
        $this->_token = $token;
        $this->_tokenGen = $_tokenGen;
        $this->_lastLogin = $lastLogin;
        $this->_joinDate = $joinDate;
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
    public static function CreateAccount($email, $fName, $lName, $password)
    {

        $passHash  = password_hash($password, PASSWORD_BCRYPT);
        $token = md5(uniqid(mt_rand(),true));
        $currentDate = date("Y-m-d H:i:s");

        $sql = "INSERT INTO Accounts 
                (Email, FirstName, LastName, PasswordHash, LoginToken, TokenGenTime, LastLogin, JoinDate)  
                VALUES(:email, :fName, :lName, :passHash, :logTok, :tokGen, :lastLog, :joinDate)";
        
//        $sql = "CALL AddUser(:email, :fName, :lName, :passHash, :logTok, :tokGen, :lastLog, :joinDate)";
        $statement = Database::connect()->prepare($sql);

        if(!$statement->execute([
            ':email' => $email,
            ':fName' => $fName,
            ':lName' => $lName,
            ':passHash' => $passHash,
            ':logTok' => $token,
            ':tokGen' => $currentDate,
            ':lastLog' => $currentDate,
            ':joinDate' => $currentDate
        ])) {
            var_dump(Database::connect()->errorInfo());
            throw new Exception("Could not create account");
        }

        $accountID = (int)Database::connect()->lastInsertId()[0];

        return new Account($accountID, $token, $currentDate, $email, $fName, $lName, $passHash
            , $currentDate, $currentDate);

    }

    /**
     * Function Login
     * @param $token_email
     * @param null $password
     * @return Account|false
     * This function facilitates the data lookup of a user who is attempting to log into the Sling Application.
     * If the user has provided a password the function will return the account data through an SQL query based
     * on the stored password.
     * If the user has not provided a password, then the system will return a new account with a login token as
     * the search criteria.
     * If the username or password do not match, the system will return false
     */
    public static function Login($token_email, $password = null)        //add validity checks
    {
        $retval = null;
        if($password) {
            $sql = "SELECT *
                FROM Accounts
                WHERE Email = :email";
            $statement = Database::connect()->prepare($sql);
            $statement->execute(array(':email' => $token_email));
            $result = $statement->fetch(PDO::FETCH_ASSOC);

            if(!password_verify($password, $result['PasswordHash']))
                $retval = false;
            else{
                $retval = new Account($result['AccountID'], $result['Email'], $result['FirstName']
                    , $result['LastName'], $result['LoginToken'], $result['TokenGenTime']
                    , $result['LastLogin'], $result['JoinDate']);

            }

        }
        else {      //no password provided, lookup based on token
            $sql = "SELECT *
                FROM Accounts
                WHERE LoginToken = :logtok";

            $statement = Database::connect()->prepare($sql);
            $statement->execute(array(':logtok' => $token_email));
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            if($result){
                $retval = new Account($result['AccountID'], $result['Email'], $result['FirstName']
                    , $result['LastName'], $result['LoginToken'], $result['TokenGenTime']
                    , $result['LastLogin'], $result['JoinDate']);
            }else{
                $retval = false;
            }
        }
        return $retval;
    }

    /**
     * Function Delete
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
        $sql = "SELECT AccountID                                                                    
                    FROM Accounts
                    WHERE LoginToken = :logtok";
        $statement = Database::connect()->prepare($sql);

        if($statement->execute(array(':logtok' => $this->_token))) {
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            #foreach($result as $row)
            #    var_dump($row);

            $this->_accountID = $result[0]["AccountID"];

            $sql = "    DELETE FROM Particpants
                    WHERE AccountID = $this->_accountID";
            $statement = Database::connect()->prepare($sql);

            if($statement->execute()) {
                $sql = "    DELETE FROM Accounts
                    WHERE AccountID = $this->_accountID";
                $statement = Database::connect()->prepare($sql);
                $statement->execute();
            }
        }
    }

    public function deleteParticipant()
    {
        $sql = "DELETE FROM Participants AS p
                            JOIN Accounts AS a
                              ON p.AccountID = a.AccountID
                WHERE p.AccountID = :accountID";

        $statement = Database::connect()->prepare($sql);
        $statement->execute(array(':accountID' => $this->_accountID));

        $this->_roomID = null;
        $this->_screenName = null;
    }
    /**
     * Function Update
     * This function will trigger whenever a user attempts to join a room, it will attempt to insert the account
     * data and the participant data to correlate with the new room and participant status.
     */
    //NEEDED:   Update function that can allow a user to edit account information
    //NEEDED:   Update Account status based on room to join and allow linked participant to join room
    //NEEDED:   Test that allows room to be created-> then account-> then update to move account and part. to room
    public function update()
    {
        $sql = "UPDATE Accounts
                SET Email = :email,
                    FirstName = :fName,
                    LastName = :lName,
                    PasswordHash = :passHash,
                    LoginToken = :logTok,
                    TokenGenTime = :tokGen,
                    LastLogin = :lastLog,
                    JoinDate = :joinDate
                WHERE AccountID = :accountID";
        $statement = Database::connect()->prepare($sql);

        if($statement->execute(array(':email' => $this->_email,
                                     ':fName' => $this->_fName,
                                     ':lName' => $this->_lName,
                                     ':passHash' => $this->_passHash,
                                     ':logTok' => $this->_token,
                                     ':tokGen' => $this->_tokenGen,
                                     ':lastLog' => $this->_lastLogin,
                                     ':joinDate' => $this->_joinDate,
                                     ':accountID' => $this->_accountID)))
        {

            //Do select statement and pull account id after account created.
            $sql = "INSERT INTO Participants
                (AccountID, RoomID, ScreenName)
                VALUES (:accountID, :roomID, :screenName)";
            $statement = Database::connect()->prepare($sql);
            if($statement->execute(array(':accountID' => $this->_accountID, ':roomID' => $this->_roomID
            , ':screenName' => $this->_screenName))) {
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                #foreach($result as $row)
                #    var_dump($row);
            }
            //create error if result is nothing
            //FOUND OUT WHAT WAS WRONG, tests failed because no room existed, ref. integrity.
            //Need REAL account info
            //NEED ACTUAL room id
        }
        else {
            DatabaseObject::Log("AccountUpdate", "Could Not Insert");
        }

        if(!$statement->execute(array(':accountID' => 101, ':roomID' => 123, ':screenName' => "TEST_SCREEN_NAME"))) {
            DatabaseObject::Log("ParticipantsUpdate", "Could Not Insert");
        }
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->_token;
    }
    /**
     * Function getJSON
     * @return string
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
        if($as_array)
            return $json;
        return json_encode($json);
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
        switch (strtolower($name)){
            case "email":
                $temp = $this->_email;
                $this->_email = $value;
                DatabaseObject::Log("Updated Account",
                    "Account: $this->_accountID \n Updated Email From: $temp to: $value");
                break;
            case "fname":
                $temp = $this->_fName;
                $this->_fName = $value;
                DatabaseObject::Log("Updated Account",
                    "Account: $this->_accountID \n Updated First Name From: $temp to: $value");
                break;
            case "lname":
                $temp = $this->_lName;
                $this->_lName = $value;
                DatabaseObject::Log("Updated Account",
                    "Account: $this->_accountID \n Updated Last Name From: $temp to: $value");
                break;
            case "passHash":
                $this->_passHash = password_hash($value, PASSWORD_BCRYPT);
                DatabaseObject::Log("Updated Account",
                    "Account: $this->_accountID \n updated password");  //should we log this?
                break;
            case "token":
                $this->_token = $value;
                $this->_tokenGen = date('Y-m-d H:i:s');
                DatabaseObject::Log("Updated Account",
                    "Account: $this->_accountID \n Updated token");
                break;
            case "lastLogin":
                $this->_lastLogin = $value;
                DatabaseObject::Log("Updated Account",
                    "Account: $this->_accountID \n Last Login: $this->_lastLogin");
                break;
            default:
                DatabaseObject::Log("Updated Account",
                    "Account: $this->_accountID \n set method using: $name wasn't valid");
        }
        return $value;
    }
}
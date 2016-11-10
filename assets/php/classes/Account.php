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
    private $_errors;
    private $_access;
    private $_login;
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
    public function __construct($accountID, $email = null, $fName = null, $lName = null, $passHash = null
        , $token = null, $_tokenGen = null, $lastLogin = null , $joinDate = null)
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

//        $this->_errors = array();
//        $this->_token = $_POST['token'];
//        $this->_tokenGen = $_POST['tokgen'];
//
//        if($reg == 0)
//        {
//            $this->_login = isset($_POST['login']) ? 1 : 0;
//            $this->_access = 0;
//            //if user presses submit, pull email from POST, if user presses back button, pull from SESSION
//            $this->_email = ($this->_login) ? $this->filter($_POST['email']) : $_SESSION['email'];
//            //If user presses submit, pull password from POST otherwise leave blank
//            $this->_password = ($this->_login) ? $this->filter($_POST['password']) : '';
//            //$this->_passHash = ($this->_login) ? crypt($this->_password) : $_SESSION['password'];
//        }
//        else
//        {
//            $this->_email = $this->filter($_POST['email']);
//            $this->_password = $this->filter($_POST['password']);
//            $this->_passHash  = crypt($this->_password);
//            $this->_fName = $_POST['fName'];
//            $this->_lName = $_POST['lName'];
//        }
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
        $currentDate = date('Y-m-d H:i:s:u');

        $sql = "INSERT INTO Accounts 
                (Email, FirstName, LastName, PasswordHash, LoginToken, TokenGenTime, LastLogin, JoinDate)  
                VALUES(:email, :fName, :lName, :passHash, :logTok, :tokGen, :lastLog, :joinDate)";
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
        return new Account($accountID, $email, $fName, $lName, $passHash
            , $token, $currentDate, $currentDate, $currentDate);
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

//                    new Account($result['AccountID'], $result['Email'], $result['FirstName']
//                    , $result['LastName'], $result['LoginToken'], $result['TokenGenTime']
//                    , $result['LastLogin'], $result['JoinDate']);
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
     * existing account based on an account email. The returned
     * accountID will then be used to remove any associated
     * participants and then will remove the account itself.
     */
    //What if account is temp and has no email?
    //Cound we use a unique RandomEmail generated for temp accounts to id it?
    //Then upon participant leaving room, we can get the account Id as they leave from the participant table joined
    //to the Accounts table, and remove the account based on the RandomEmail that is associated with it?

    //NEEDED:   Delete function to remove a participant but not an Account using same method above
    //NEEDED:   Delete function to remove temporary accounts and associated participants
    public function delete()
    {
        $sql = "SELECT AccountID                                                                    
                    FROM Accounts
                    WHERE Email = :email";
        $statement = Database::connect()->prepare($sql);

        if($statement->execute(array(':email' => $this->_email))) {
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
        $sql = "INSERT INTO Accounts
                (Email, FirstName, LastName, PasswordHash, LoginToken, TokenGenTime, LastLogin, JoinDate)  
                VALUES(:email, :fName, :lName, :passHash, :logTok, :tokGen, :lastLog, :joinDate)";
        $statement = Database::connect()->prepare($sql);

        if($statement->execute(array(':email' => $this->_email, ':fName' => $this->_fName, ':lName' => $this->_lName
        , ':passHash' => $this->_passHash, ':logTok' => $this->_token, ':tokGen' => $this->_tokenGen
        , ':lastLog' => date('Y-m-d H:i:s'), ':joinDate' => date('Y-m-d H:i:s')))) {
            $sql = "SELECT AccountID
                    FROM Accounts
                    WHERE Email = :email";
            $statement = Database::connect()->prepare($sql);

            if($statement->execute(array(':email' => $this->_email))) {
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                #foreach($result as $row)
                #    var_dump($row);

                $this->_accountID = $result[0]["AccountID"];
            }
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
     * Function getJSON
     * @return string
     * This function allows the Accounts type to be encoded.
    */
    public function getJSON()
    {
        $json = [];
        $json['type'] = "Accounts";
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
    /**
     * Function Process
     * @return int
     * This Function initiates the Update function based on the Validation of the
     * Data and the Token.
     */
    public function process()
    {
        if($this->isTokenValid() && $this->isDataValid()) {
            $this->update();
        }
        return count($this->_errors) ? 0 : 1;   //0 if no errors
    }
    /**
     * Function isLoggedIn
     * @return mixed
     * This Function returns the _access variable, which determines whether the
     * Account needs to be registered or logged into.
     */
    public function isLoggedIn()
    {
        ($this->_login)? $this->verifyPost() : $this->verify_Session();
        return $this->_access;
    }
    /**
     * Function Filter
     * @param $var
     * @return mixed
     * This Function uses a regular expression to assure valid user input.
     */
    public function filter($var)
    {
        return preg_replace('/[^a-zA-Z0-9@.]/', '', $var);
    }
    /**
     * Function VerifyPost
     * This function requires verification for user input, it
     * will return an exception if an invalid value is passed in
     * the Submission, Data, Username or Password fields.
     */
    public function verifyPost()
    {
        try
        {
            if(!$this->isTokenValid())
                throw new Exception('Invalid Form Submission');
            if(!$this->isDataValid())
                throw new Exception('Invalid Form Data');
            if(!$this->verifyDatabase())
                throw new Exception('Invalid Username/Password');

            $this->_access = 1;
            $this->registerSession();
        }
        catch (Exception $e)
        {
            $this->_errors[] = $e->getMessage();
        }
    }

    public function verify_Session()
    {
        if($this->sessionExist() && $this->verifyDatabase())
            $this->_access = 1;
    }

    public function verifyDatabase()
    {
        $verify = false;

        $sql = "SELECT Email, PasswordHash
                FROM Accounts
                WHERE Email = :email";

        $statement = Database::connect()->prepare($sql);
        $statement->execute(array(':email' => $this->_email));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        #foreach($result as $row)
        #   var_dump($row);

        if($this->_email == $result[0]["Email"] && password_verify($this->_password, $result[0]["PasswordHash"]))
            $verify = true;

        return $verify;
    }

    public function isDataValid()
    {
        $emailExp = "/[a-zA-Z0-9.]+@[a-zA-Z0-9]+.[a-zA-Z]+/";
        $passExp =  "/[a-zA-Z0-9_.+]/";

        //add validation for first and last name?
        return (preg_match($emailExp, $this->_email) && preg_match($passExp, $this->_password)) ? 1 : 0;
    }

    public function isTokenValid()
    {
        return (!isset($_SESSION['token']) || $this->_token != $_SESSION['token']) ? 0 : 1;
    }

    public function registerSession()
    {
        $_SESSION['email'] = $this->_email;
        $_SESSION['password'] = $this->_password;   //change to passhash

        #var_dump($_SESSION);
    }

    public function sessionExist()
    {
        return (isset($_SESSION['email']) && isset($_SESSION['password'])) ? 1:0;   //change to passhash
    }

    public function showErrors()
    {
        foreach($this->_errors as $key=>$value)
            echo $value."<br>";
    }

    function __set($name, $value)
    {
        switch (strtolower($name)){
            case "email":
                $this->_email = $value;
                break;
            case "fname":
                $this->_fName = $value;
                break;
            case "lname":
                $this->_lName = $value;
                break;

        }
        return $value;
    }


}

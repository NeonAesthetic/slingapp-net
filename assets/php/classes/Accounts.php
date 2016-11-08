<?php

/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 11/6/2016
 * Time: 9:21 AM
 */

set_include_path(realpath($_SERVER["DOCUMENT_ROOT"]) . "/assets/php/");

require_once "classes/Database.php";
require_once "interfaces/DatabaseObject.php";

class Accounts extends DatabaseObject
{
    private $_fName;
    private $_lName;
    private $_email;
    private $_password;
    private $_passHash;
    private $_errors;
    private $_access;       //1 if account exists
    private $_login;
    private $_token;
    private $_tokenGen;
    private $_lastLogin;
    private $_joinDate;
    private $_accountID;
    private $_roomID;
    private $_screenName;

    public function __construct($reg)
    {
        $this->_errors = array();
        $this->_token = $_POST['token'];
        $this->_tokenGen = $_POST['tokgen'];

        if($reg == 0)
        {
            $this->_login = isset($_POST['login']) ? 1 : 0;
            $this->_access = 0;
            //if user presses submit, pull email from POST, if user presses back button, pull from SESSION
            $this->_email = ($this->_login) ? $this->filter($_POST['email']) : $_SESSION['email'];
            //If user presses submit, pull password from POST otherwise leave blank
            $this->_password = ($this->_login) ? $this->filter($_POST['password']) : '';
            //$this->_passHash = ($this->_login) ? crypt($this->_password) : $_SESSION['password'];
        }
        else
        {
            $this->_email = $this->filter($_POST['email']);
            $this->_password = $this->filter($_POST['password']);
            $this->_passHash  = crypt($this->_password);
            $this->_fName = $_POST['fName'];
            $this->_lName = $_POST['lName'];
        }
    }

    public function delete()
    {
        $sql = "SELECT AccountID
                    FROM Accounts
                    WHERE Email = :email";
        $statement = Database::connect()->prepare($sql);

        if($statement->execute(array(':email' => $this->_email)))
        {
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            #foreach($result as $row)
            #    var_dump($row);

            $this->_accountID = $result[0]["AccountID"];

            $sql = "    DELETE FROM Particpants
                    WHERE AccountID = $this->_accountID";
            $statement = Database::connect()->prepare($sql);

            if($statement->execute())
            {
                $sql = "    DELETE FROM Accounts
                    WHERE AccountID = $this->_accountID";
                $statement = Database::connect()->prepare($sql);
                $statement->execute();
            }
        }
    }

    public function update()
    {
        #echo "Updated";
        $sql = "INSERT INTO Accounts
                (Email, FirstName, LastName, PasswordHash, LoginToken, TokenGenTime, LastLogin, JoinDate)  
                VALUES(:email, :fName, :lName, :passHash, :logTok, :tokGen, :lastLog, :joinDate)";

        $statement = Database::connect()->prepare($sql);
        if($statement->execute(array(':email' => $this->_email, ':fName' => $this->_fName, ':lName' => $this->_lName, ':passHash' => $this->_passHash, ':logTok' => $this->_token, ':tokGen' => $this->_tokenGen, ':lastLog' => date('Y-m-d H:i:s'), ':joinDate' => date('Y-m-d H:i:s'))))
        {
            $sql = "SELECT AccountID
                    FROM Accounts
                    WHERE Email = :email";
            $statement = Database::connect()->prepare($sql);

            if($statement->execute(array(':email' => $this->_email)))
            {
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                #foreach($result as $row)
                #    var_dump($row);

                $this->_accountID = $result[0]["AccountID"];
            }
            //Do select statement and pull accnt id after account created.
            $sql = "INSERT INTO Participants
                (AccountID, RoomID, ScreenName)
                VALUES (:accountID, :roomID, :screenName)";
            $statement = Database::connect()->prepare($sql);
            if($statement->execute(array(':accountID' => $this->_accountID, ':roomID' => $this->_roomID, ':screenName' => $this->_screenName)))
            {
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                #foreach($result as $row)
                #    var_dump($row);
            }
            //create error if result is nothing
            //FOUND OUT WHAT WAS WRONG, tests faile dbecause no room existed, ref. integrity.
            //Need REAL account info
            //NEED ACTUAL room id

            echo "Inserted Acc";
        }
        else
        {
            DatabaseObject::Log("AccountUpdate", "Could Not Insert");
        }

        if(!$statement->execute(array(':accountID' => 19, ':roomID' => 776, ':screenName' => "Derp")))
        {
            echo "Uh oh";
            DatabaseObject::Log("ParticipantsUpdate", "Could Not Insert");
        }
        else
            echo "Inserted Part";
    }

    public function getJSON()
    {
        $json = [];
        $json['type'] = "Accounts";
        return json_encode($json);
    }

    public function process()
    {
        if($this->isTokenValid() && $this->isDataValid()) {
            $this->update();
        }

        return count($this->_errors) ? 0 : 1;   //0 if no errors
    }

    public function isLoggedIn()
    {
        ($this->_login)? $this->verifyPost() : $this->verify_Session();

        return $this->_access;
    }

    public function filter($var)
    {
        return preg_replace('/[^a-zA-Z0-9@.]/', '', $var);
    }

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
}

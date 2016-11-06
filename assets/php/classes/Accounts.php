<?php

/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 11/6/2016
 * Time: 9:21 AM
 */
class Accounts extends DatabaseObject
{
    private $_accountID;
    private $_email;
    private $_firstName;
    private $_lastName;
    private $_passwordHash;
    private $_loginToken;
    private $_tokenGenTime;
    private $_lastLogin;
    private $_joinDate;

    public function __construct($accountID, $email = null, $firstName = null, $lastName = null, $passwordHash = null, $loginToken, $tokenGenTime, $lastLogin, $joinDate)
    {
        $this->_accountID = $accountID;
        $this->_email = $email;
        $this->_firstName = $firstName;
        $this->_lastName = $lastName;
        $this->_passwordHash = $passwordHash;
        $this->_loginToken = $loginToken;
        $this->_tokenGenTime = $tokenGenTime;
        $this->_lastLogin = $lastLogin;
        $this->_joinDate = $joinDate;

        if($email != null)      //check to see if account has been created
            $this->_passwordHash;
    }
    public function delete()
    {
        $sql = "    DELETE FROM Accounts
                    WHERE AccountID = $this->_accountID";
        $statement = Database::connect()->prepare($sql);
        $statement->execute();
    }

    public function update()
    {
        $statement = Database::connect();
        $statement->query("INSERT INTO Accounts (Email, FirstName, LastName, PasswordHash, LoginToken, TokenGenTime, LastLogin, JoinDate)
                    VALUES(':email', ':first', ':last', ':pass', 'logintok', 'tokenGenTime', 'lastLogin', 'joinDate')");

        if(!$statement->execute([":email" => $this->_email, ":first"=>$this->_firstName, ":last"=>$this->_lastName, ":pass"=>$this->_passwordHash, ":logintok"=>$this->_loginToken, ":tokenGenTime"=>$this->_tokenGenTime, ":lastLogin"=>$this->_lastLogin, ":joinDate"=>$this->_joinDate]))
            $this->errors[] = 'Could Not Insert';
    }

    public function getJSON()
    {
        $json = [];
        $json['type'] = "Accounts";
        return json_encode($json);
    }

    //both parameters need to be hashed using crypt function, don't send plain text!
    public function verifyPassword($user_input, $hashed_password)
    {
        return (hash_equals($hashed_password, crypt($user_input, $hashed_password))) ? 1 : 0;       #1 = password verified
    }

    public function createToken($data)
    {

    }

    public function process()
    {}

    public function filter($var)    //filter out any unwanted characters in variables
    {
        return preg_replace('/[^a-zA-Z0-9@.]/', '', $var);
    }

    public function show_errors()
    {
        echo "<h3>Errors</h3>";

        foreach($this->errors as $key=>$value)
            echo $value."<br>";
    }

    public function valid_data()
    {
        if(empty($this->email))
            $this->errors[] = 'Invalid email';
        if(empty($this->_passwordHash))
            $this->errors[] = 'Invalid password';

        return count($this->errors) ? 0 : 1;
    }

    public function valid_token()
    {
        if(!isset($_SESSION['token']) || $this->token != $_SESSION['token'])

    }

}

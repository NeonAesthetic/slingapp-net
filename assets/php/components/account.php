<?php
/**
 * Created by PhpStorm.
 * User: Ian, Isaac
 * Date: 11/8/16
 * Time: 8:19 AM
 */

require_once realpath($_SERVER["DOCUMENT_ROOT"]) . "/assets/php/components/StandardHeader.php";
require_once "classes/Account.php";

$p = GetParams("action", "email", "fname", "lname", "password");

session_start();

$GLOBALS['access'] = 0;
$GLOBALS['login'] = 0;

switch ($p['action'])
{
    case "Sign Up":     //value must equal the name of the button to register (case sensitive)
        if ($account = process($p))
        {
            $_SESSION['token'] = $account->getToken();
            echo "Registration was successful!";
        }
        else
        {
            DatabaseObject::Log("Register Account", "Account could not be created");
            echo "Registration was unsuccessful";
        }
    break;
    case "login":
        //if user pressed submit button, login is set to true, otherwise set to 0 (if they press back)
        $GLOBALS['login'] = ($p['action'] == 'login') ? 1 : 0;
        //if user presses submit, pull email from POST, if user presses back button, pull from SESSION
            $p['email'] = ($p['action'] == 'login') ? $_POST['email'] : $_SESSION['email'];
        //If user presses submit, pull password from POST otherwise leave blank
            $p['password'] = ($GLOBALS['login']) ? $p['password'] : '';

        if($GLOBALS['login'] == 1 && $p['password'])
        {
            $account = Account::Login($p['email'], $p['password']);

            if($account && isLoggedIn($p))
            {
                $_SESSION['token'] = $account->getToken();
                echo "Successfully logged in using password!";
            }
            else
                echo "unsuccessfully logged in using password";
        }
        else    //no password = use token to login
            if(Account::Login($p['token']))
            {
                echo "Successfully logged in using token!";
                $_SESSION['token'] = $p['token'];
            }
            else
                echo "Unable to login through token";
        break;
  //pass in token and return JSON account object
    case "getcookie": {
        break;
    }
    case "newtoken": {
        break;
    }
}

/**
 * Function Process
 * @param array[string]string
 * @return Account|false
 * This Function initiates the Update function based on the Validation of the
 * Data and the Token.
 */
function process($p)
{
    $retval = false;

    if(isTokenValid($p['token']) && isEmailValid($p['email']))
        $retval = Account::CreateAccount($p['email'], $p['fname'], $p["lname"], $p["password"]);

    return $retval;
}
/**
 * Function isLoggedIn
 * @param array[string]string
 * @return boolean
 * This Function returns the _access variable, which determines whether the
 * Account needs to be registered or logged into.
 */
function isLoggedIn($p)
{
    ($GLOBALS['login']) ? verifyPost($p) : verifySession($p);
    return $GLOBALS['access'];
}
/**
 * Function VerifyPost
 * @param array[string]string
 * This function requires verification for user input, it
 * will return an exception if an invalid value is passed in
 * the Submission, Data, Username or Password fields.
 */
function verifyPost($p)
{
    try
    {
        if(!isTokenValid($p['token']))
            throw new Exception('Invalid Form Submission');
        if(!isEmailValid($p['email']))
            throw new Exception('Invalid Form Data');
        if(!verifyDatabase($p))
            throw new Exception('Invalid Username/Password');

        $GLOBALS['access'] = 1;
        registerSession($p);
    }
    catch (Exception $e)
    {
        echo $e;
    }
}

/**
 * Function verifySession
 * @param array[string]string
 * This function requires verification for user input, it
 * will return an exception if an invalid value is passed in
 * the Submission, Data, Username or Password fields.
 */
function verifySession($p)
{
    if(sessionExist() && verifyDatabase($p))
        $GLOBALS['access'] = 1;
}

function verifyDatabase($p)
{
    $verify = false;

    $sql = "SELECT Email, PasswordHash
                FROM Accounts
                WHERE Email = :email";

    $statement = Database::connect()->prepare($sql);
    $statement->execute(array(':email' => $p['email']));
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    if($p['email'] == $result[0]["Email"] && password_verify($p['password'], $result[0]["PasswordHash"]))
        $verify = true;

    return $verify;
}

function isEmailValid($email)
{
    $emailExp = "/[a-zA-Z0-9.]+@[a-zA-Z0-9]+.[a-zA-Z]+/";
    return preg_match($emailExp, $email) ? 1 : 0;
}

function isTokenValid($token)
{
    return (isset($_SESSION['token']) || $token == $_SESSION['token']) ? 1 : 0;
}

function registerSession($p)
{
    $_SESSION['email'] = $p['email'];
    $_SESSION['password'] = $p['password'];
}

function sessionExist()
{
    return (isset($_SESSION['email']) && isset($_SESSION['password'])) ? 1:0;
}
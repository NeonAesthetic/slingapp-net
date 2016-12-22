<?php
/**
 * Created by PhpStorm.
 * User: Ian, Isaac
 * Date: 11/8/16
 * Time: 8:19 AM
 */

#echo session_id();

require_once realpath($_SERVER["DOCUMENT_ROOT"]) . "/assets/php/components/StandardHeader.php";

require_once "classes/Account.php";
$p = GetParams("action", "email", "fname", "lname", "pass1", "pass2", "token");

$GLOBALS['access'] = 0;
$GLOBALS['login'] = 0;
switch ($p['action']) {
    case "register":     //value must equal the name(value) of the submit button from the HTML FORM to register
        $retval = false;
        if ($account = process($p)) {
            $retval = $account->getJSON();
        } else
            Logger::Log(__FILE__, SLN_REGISTER, NULL, NULL, "Account Registration Unsuccessful");

        echo $retval;
        break;
    case "tempregister":
        $account = Account::CreateAccount();

        echo $account->getJSON();
        break;

    case "login":
        $retval = json_encode(null);
        //if user pressed submit button, login is set to true, otherwise set to 0 (if they press back)
        $GLOBALS['login'] = ($p['action'] == 'login') ? 1 : 0;
        //if user presses submit, pull email from POST, if user presses back button, pull from SESSION
        $p['email'] = ($p['action'] == 'login') ? $p['email'] : $_SESSION['email'];
        //If user presses submit, pull password from POST otherwise leave blank
        $p['pass1'] = ($GLOBALS['login']) ? $p['pass1'] : '';
        if ($GLOBALS['login'] == 1 && $p['pass1']) {
            $account = Account::Login($p['email'], $p['pass1']);
            if ($account && isLoggedIn($p)) {
                $token = $account->getToken();
                $_SESSION['token'] = $account->getToken();
                #echo "Successfully logged in using password!";
                $retval = $account->getJSON();
            } else {
            }
        } else {   //no password = use token to login
            if ($account = Account::Login($p['token'])) {
                #echo "Successfully logged in using token!";
//                $_SESSION['token'] = $p['token'];
                $retval = $account->getJSON();
            } else {
                echo $p["token"];
            }
        }
        echo $retval;   //ajax expects echo not return
        break;
    case "changepass": {
    }
        break;
    //pass in token and return JSON account object
    case "delete": {
    }
        break;

    case "getcookie": {
    }
        break;
    case "newtoken": {
    }
        break;
    case "nocookie": {
        $account = Account::CreateAccount();
        echo $account->getJSON();
    }
        break;
    case "tokenisvalid": {
        if (Account::Login($p['token']) != false) {
            echo json_encode(["valid" => true]);
        } else {
            echo json_encode(["valid" => false]);
        }
    }
        break;
    default:
        Logger::Log(__FILE__, "action not valid",
            "action wasn't valid");
}
/**
 * Function Process
 * @param array [string]string
 * @return Account|false
 * This Function initiates the Update function based on the Validation of the
 * Data and the Token.
 */
function process($p)
{
    $retval = false;
    if (isTokenValid($p['token']) && isDataValid($p))
        $retval = Account::CreateAccount($p['email'], $p['fname'], $p["lname"], $p["pass1"]);
    return $retval;
}

/**
 * Function isLoggedIn
 * @param array [string]string
 * @return boolean
 * This Function returns the _access variable, which determines whether the
 * Account-Tests needs to be registered or logged into.
 */
function isLoggedIn($p)
{
    ($GLOBALS['login']) ? verifyPost($p) : verifySession($p);
    return $GLOBALS['access'];
}

/**
 * Function VerifyPost
 * @param array [string]string
 * This function requires verification for user input, it
 * will return an exception if an invalid value is passed in
 * the Submission, Data, Username or Password fields.
 */
function verifyPost($p)
{
    try {
        if (!isTokenValid($p['token']))
            throw new Exception('Invalid Form Submission');
        if (!(filter_var($p['email'], FILTER_VALIDATE_EMAIL)))
            throw new Exception('Invalid Form Data');
        if (!verifyDatabase($p))
            throw new Exception('Invalid Username/Password');
        $GLOBALS['access'] = 1;
        registerSession($p);
    } catch (Exception $e) {
        echo $e;
    }
}

/**
 * Function verifySession
 * @param array [string]string
 * This function requires verification for user input, it
 * will return an exception if an invalid value is passed in
 * the Submission, Data, Username or Password fields.
 */
function verifySession($p)
{
    if (sessionExist() && verifyDatabase($p))
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
    if ($p['email'] == $result[0]["Email"] && password_verify($p['pass1'], $result[0]["PasswordHash"]))
        $verify = true;
    return $verify;
}

function isDataValid($p)
{
    $emailExp = "/[a-zA-Z0-9.]+@[a-zA-Z0-9]+.[a-zA-Z]+/";
    return preg_match($emailExp, $p['email']) && $p['pass1'] == $p['pass2'] ? 1 : 0;
}

function isTokenValid($token)
{
    return (isset($_SESSION['token']) || $token == $_SESSION['token']) ? 1 : 0;
}

function registerSession($p)
{
    $_SESSION['email'] = $p['email'];
    $_SESSION['pass1'] = $p['pass1'];
}

function sessionExist()
{
    return (isset($_SESSION['email']) && isset($_SESSION['pass1'])) ? 1 : 0;
}
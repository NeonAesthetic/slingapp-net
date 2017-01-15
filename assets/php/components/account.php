<?php
/**
 * Created by PhpStorm.
 * User: Ian, Isaac
 * Date: 11/8/16
 * Time: 8:19 AM
 */

require_once realpath($_SERVER["DOCUMENT_ROOT"]) . "/assets/php/components/StandardHeader.php";
require_once "classes/Account.php";

$p = GetParams("action", "email", "fname", "lname", "pass1", "pass2", "token");

switch ($p['action']) {
    case "register":
        $retval = false;
        if ($account = process($p))
            $retval = $account->getJSON();

        echo $retval;
        break;
    case "login":
        $retval = json_encode(null);
            $account = Account::Login($p['email'], $p['pass1']);

            if ($account && verifyPost($p))
                $retval = $account->getJSON();

        echo $retval;   //ajax expects echo not return
        break;

    case "changepass": {
    }
        break;
    //pass in token and return JSON account object
    case "delete": {
    }
        break;
    case "nocookie": {
        $account = Account::CreateAccount();
        echo $account->getJSON();
    }
        break;
    case "tokenisvalid": {
        echo (Account::Login($p['token']) != false) ?
            json_encode(["valid" => true]) : json_encode(["valid" => false]);
    }
        break;
    default:
        echo null;  //return a non JSON object to trigger an AJAX failure
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
    return (isDataValid($p)) ?
        Account::CreateAccount($p['email'], $p['fname'], $p['lname'], $p['pass1'], $p['token']) : false;
}

/**
 * Function VerifyPost
 * @param array [string]string
 * @return bool
 * This function requires verification for user input, it
 * will return an exception if an invalid value is passed in
 * the Submission, Data, Username or Password fields.
 */
function verifyPost($p)
{
    $retval = false;
    try {
        if (!(filter_var($p['email'], FILTER_VALIDATE_EMAIL)))
            throw new Exception('Invalid Form Data');
        if (!verifyDatabase($p))
            throw new Exception('Invalid Username/Password');
        $retval = true;
    } catch (Exception $e) {
        echo $e;
    }

    return $retval;
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
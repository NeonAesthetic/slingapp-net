<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 11/10/16
 * Time: 9:17 AM
 *
 * Test Name: All Account Tests
 * Description: Runs all the tests required to make sure Account works as intended: Login, Create, set, get, update
 */

require_once "classes/Account.php";

/**
 *          TEST LOGIN STATIC METHOD
 */
{   //login with invalid credentials
    mark();
    $account = Account::Login("emaildoesntExist@oit.edu", "testpassword");
    assert($account == false, "Invalid login returns false");
    mark("Invalid User/pass login");

    //login with an invalid token
    $account = Account::Login("test token");
//var_dump($account);
    assert($account == false, "Invalid login returns false");
    mark("Invalid token login");

    //login with an existing account
    $password = password_hash('pass', PASSWORD_BCRYPT);
    $token = '654f1f13d2fa0a49a28d297a10a35f56';
    //create temp account for successful login
    $sql = "INSERT INTO Accounts
                (Email, FirstName, LastName, PasswordHash, LoginToken, TokenGenTime, LastLogin, JoinDate)
                VALUES('testemail@test.com', 'first', 'last', :pass, :token, '2016-11-10 07:47:06', '2016-11-10 07:47:06', '2016-11-10 07:47:06')";
    $statement = Database::connect()->prepare($sql);
    if(!$statement) var_dump(Database::connect()->errorInfo());

    $statement->execute(array(':pass' => $password, ':token' => $token));

    mark();
    $account = Account::Login("testemail@test.com", "pass");
    mark("login with email/pass");
    $first = $account->getName()["First"];
    $last = $account->getName()['Last'];
    $email = $account->getEmail();
    assert($first == "first", "First name is first");
    assert($last == "last", "Last name is last");
    assert($email == "testemail@test.com", "Email is testemail@test.com");

    //login with an existing session
    mark();
    $account = Account::Login("654f1f13d2fa0a49a28d297a10a35f56");
    mark("login with token");
    $first = $account->getName()["First"];
    $last = $account->getName()['Last'];
    $email = $account->getEmail();
    assert($first == "first", "First name is first");
    assert($last == "last", "Last name is last");
    assert($email == "testemail@test.com", "Email is testemail@test.com");

    cleanup();
}
/**
 *          CREATE NEW ACCOUNT
 */
{
    mark();
    $account = Account::CreateAccount("testemail@test.com", "Bob", "Marley", "password");
    mark("Create new account");

    $first_name = $account->getName()["First"];
    $last_name = $account->getName()["Last"];
    $id = $account->getJSON(true)["ID"];
    assert($first_name == "Bob", "First name is Bob");
    assert($last_name == "Marley", "Last name is Marley");

    $account->email = "email@test.com";

    assert($account->getEmail() == "email@test.com", "email@test.com is 'test'");

//    assert();
    cleanup();
}


/**
 *          Set Account
 */
{
    $account = Account::CreateAccount("testnewemail@test.com", "Bob", "Marley", "password");

    mark();
    $account->email = "replace@test.com";
    mark("update email");
    $account->fname = "ozzy";
    $account->lname = "osbourne";
    mark();
    $account->passhash = "newpass";
    mark("update password");
    $account->token = 'new';    //doesn't matter what you pass it

    $account = Account::Login("replace@test.com", "newpass");
    assert($account != null, "login success with updated credentials");

    cleanup();
}

/**
 *          Get Account
 */
{
    $account = Account::CreateAccount("testnewemail@test.com", "Bob", "Marley", "password");
    $email = $account->getEmail();
    $fname = $account->getName()['First'];
    $lname = $account->getName()['Last'];
    $screenname = $account->getScreenName();
    $token = $account->getToken();
    $accountID = $account->getAccountID();
    $json = $account->getJSON(true);

    #var_dump($json);

    assert($accountID == $json['ID'], "json ID is equal to account's id");
    assert($email == $json['Email'], "json email is equal to account's email");
    assert($fname == $json['FirstName'], "json first name is equal to account's");
    assert($lname == $json['LastName'], "json last name is equal to account's");
    assert($token == $json['LoginToken'], "json token is equal to account's");

    cleanup();
}
/**
 *          Update Account
 */

//same as set account

/**
 *          Delete Account
 */
    $account = Account::CreateAccount("testnewemail@test.com", "Bob", "Marley", "password");
    mark();
    assert($account->delete(), "Account deleted successfully");
    mark("Account deletion");
/**
 *          Create Participant
 */

/**
 *          Set Participant
 */

/**
 *          Get Participant
 */

/**
 *          Update Participant
 */

/**
 *          Delete Participant
 */

//NEED BETTER CLEANUP
function cleanup(){
    try{
        Database::connect()->query("DELETE 
                                    FROM Accounts
                                    WHERE (Email = 'testemail@test.com')
                                    OR (Email = 'email@test.com')
                                    OR (Email = 'replace@test.com')
                                    OR (Email = 'testnewemail@test.com')
                                    OR (Email = 'newer@gmail.com')");
    }catch (Exception $e){}
}
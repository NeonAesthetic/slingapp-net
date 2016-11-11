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

//    $account = Account::CreateAccount("testnewemail@test.com", "Bob", "Marley", "password");
//    $email = $account->getEmail();
//
//    $account->email = "replace@test.com";
//    $account->fname = "ozzy";
//    $account->lname = "osbourne";
//    $account->passhash = "newpass";
//    $account->token = 'new';    //doesn't matter what you pass it
//
//    $sql = ("SELECT *
//             FROM Accounts
//             WHERE Email='replace@test.com'");
//
//    $statement = Database::connect()->prepare($sql);
//    $statement->execute();
//    $result = $statement->fetch(PDO::FETCH_ASSOC);
//
//    var_dump($result);
//    $account = Account::Login("replace@test.com", "newpass");
//
//    assert($email == "replace@test.com", "email changed to replace@test.com");
//    assert($account)
//    $statement = Database::connect()->query("SELECT PasswordHash FROM Accounts WHERE Email='email@test.com'");
//    $statement->execute();
//    $result = $statement->fetch(PDO::FETCH_ASSOC);
//
//    $passhash = $account->getPassHash();
//    var_dump($email);
}

/**
 *          Get Account
 */

/**
 *          Update Account
 */

/**
 *          Delete Account
 */

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
                                    OR (Email = 'testnewemail@test.com')");
    }catch (Exception $e){}
}
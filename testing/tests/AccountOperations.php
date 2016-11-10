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
{
    mark();
    $account = Account::Login("emaildoesntExist@oit.edu", "testpassword");
    assert($account == false, "Invalid login returns false");
    mark("Invalid User/pass login");

    $account = Account::Login("test token");
//var_dump($account);
    assert($account == false, "Invalid login returns false");
    
    mark("Invalid token login");

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
    $id = $account->getJSON(true);
    assert($first_name == "Bob", "First name is Bob");
    assert($last_name == "Marley", "Last name is Marley");

    $account->email = "test";

    assert($account->getEmail() == "test", "Email is 'test'");
}


/**
 *          Set Account
 */

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
    Database::connect()->query("DELETE FROM Accounts WHERE Email = 'testemail@test.com'");
}

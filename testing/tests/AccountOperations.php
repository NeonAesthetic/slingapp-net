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
 *      TEST LOGIN STATIC METHOD
 */
$account = Account::Login("emaildoesntExist@oit.edu", "testpassword");
assert($account == false, "Invalid login returns false");

$account = Account::Login("test token");
//var_dump($account);
assert($account == false, "Invalid login returns false");
/***********************************************
 *
 **/


/**
 *          CREATE NEW ACCOUNT
 */

$account = Account::CreateAccount("testemail@test.com","Bob", "Marley", "password");

$first_name = $account->getName()["First"];
$last_name = $account->getName()["Last"];
assert($first_name == "Bob", "First name is Bob");
assert($last_name == "Marley", "Last name is Marley");

$account->email = "test";

assert($account->getEmail() == "test", "Email is 'test'");

function cleanup(){
    Database::connect()->query("DELETE FROM Accounts WHERE Email = 'testemail@test.com'");
}

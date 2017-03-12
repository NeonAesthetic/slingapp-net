<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 11/27/2016
 * Time: 8:04 PM
<<<<<<< HEAD
 * Test Name: Create Account and Login
 * Test Description: Creates an Account and Logs in with that account
=======
 * Test Name: NOINCLUDE
>>>>>>> working
 */


require_once "classes/Account.php";
<<<<<<< HEAD

$accounts_to_cleanup = [];

const EMAIL     = "test_email@email.com";
const FNAME     = "Test";
const LNAME     = "Email";
const PASSWORD  = "2384muv68#^#$&BV$^#&ERG";

$accoutn = Account::Login(EMAIL, PASSWORD);
echo $accoutn->getAccountID();

mark();
$account = Account::CreateAccount();
$accounts_to_cleanup[] = $account;
mark("Create Blank Account");



$token = $account->getToken();
=======
mark();

$a = Account::CreateAccount();
setcookie("Token", $a->getToken(), time()+31104000);
$a = Account::Login($_COOKIE["Token"]);
mark("Create Account");

$token = $a->getToken();
>>>>>>> working

mark();
$account = Account::Login($token);
mark("Login with Token");

<<<<<<< HEAD




mark();
$account     = Account::CreateAccount(EMAIL, FNAME, LNAME, PASSWORD);
mark("Create Filled Account");
$token       = $account->getToken();

mark();
$account     =   $account->Login(EMAIL, PASSWORD);
mark("Login with email and password");
assert(EMAIL === $account->getEmail(), "Emails match");
assert(FNAME === $account->getName()["First"], "First Names match");
assert(LNAME === $account->getName()["Last"], "Last Names match");

Account::Login(EMAIL, PASSWORD)->delete();





function cleanup() {
    global $accounts_to_cleanup;

    $number_removed = 0;

    foreach ($accounts_to_cleanup as $account){
        if($account->delete()) $number_removed++;
    }
}
=======
echo "Account ID: " . $a->getAccountID() . "<br>";
echo "Account Screenname: ". $a->getScreenName();
>>>>>>> working

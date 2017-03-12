<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 11/27/2016
 * Time: 8:04 PM
 * Test Name: Account JSON
 * Test Description: fsfdf
 */


require_once "classes/Account.php";

$accounts_to_cleanup = [];

const EMAIL     = "test_email@email.com";
const FNAME     = "Test";
const LNAME     = "Email";
const PASSWORD  = "2384muv68#^#$&BV$^#&ERG";

$account = Account::Login(EMAIL, PASSWORD);

echo $account->getJSON();

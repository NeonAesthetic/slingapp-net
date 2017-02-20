<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 11/27/2016
 * Time: 7:58 PM
 * Test Name: Get Snowflake From DB
 */


require_once "classes/Database.php";
require_once "classes/Account.php";

$a = Account::CreateAccount("testemail");



mark();
echo Database::connect()->query("SELECT AccountID FROM Accounts LIMIT 1")->fetch()[0] . "<br>";
mark("fetch snowflake");


function cleanup(){
    Database::connect()->query("DELETE FROM Accounts WHERE Email= 'testemail'");
}

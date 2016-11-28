<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 11/28/16
 * Time: 11:14 AM
 * Test Name: Log Snowflake and Access Logs
 *
 */

require_once "interfaces/DatabaseObject.php";
require_once "classes/Account.php";

$account1 = Account::CreateAccount();
$snowflake1 = $account1->getAccountID();

$account2 = Account::CreateAccount();
$snowflake2 = $account2->getAccountID();

$account3 = Account::CreateAccount();
$snowflake3 = $account3->getAccountID();


for ($i = 0; $i < 1000; $i++){
    DatabaseObject::Log(__FILE__, "test", "Testing logging", $snowflake1);
    DatabaseObject::Log(__FILE__, "test", "Testing logging", $snowflake2);
    DatabaseObject::Log(__FILE__, "test", "Testing logging", $snowflake3);
}

mark();
$results = Database::connect()->query("SELECT COUNT(*) FROM Logs WHERE Snowflake = $snowflake1")->fetch()[0];
mark("Fetch $results Results");


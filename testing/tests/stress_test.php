<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 11/10/16
 * Time: 12:30 PM
 * Test Name: Stress Test
 * Description: Adds 20 accounts
 */

require_once "classes/Account.php";

$ids = [];
mark();
for ($i = 0; $i < 20; $i++){
    $account = Account::CreateAccount("email$i", "fname$i", "lname$i", "password$i");
    $ids[] = $account->getJSON(true)["ID"];
}
mark("Insert 20 accounts");

$sql = "DELETE FROM Accounts WHERE AccountID = :id";
$statement = Database::connect()->prepare($sql);

foreach ($ids as $id){
    $statement->execute([":id"=>$id]);
}

function cleanup(){
    Database::connect()->exec("DELETE FROM Accounts WHERE Email LIKE 'email[0-9]'");
}
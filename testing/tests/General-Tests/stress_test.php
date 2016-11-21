<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 11/10/16
 * Time: 12:30 PM
 * Test Name: Stress Test
 * Description: Adds 20 accounts
 */

require_once "classes/Account-Tests.php";
require_once "classes/RoomCode.php";

$ids = [];
//mark();
//for ($i = 0; $i < 20; $i++){
//    $account = Account-Tests::CreateAccount("email$i", "fname$i", "lname$i", "password$i");
//    $ids[] = $account->getJSON(true)["ID"];
//}
//mark("Insert 20 accounts");
mark();
$thing = RoomCode::generate_code();
echo $thing . "<br>";
mark("Generate Room Code");

$sql = "DELETE FROM Accounts WHERE AccountID = :id";
$statement = Database::connect()->prepare($sql);

foreach ($ids as $id){
    $statement->execute([":id"=>$id]);
}

mark();
$thing = substr(hash("sha256", "fajskfnaksfaksjdfnaksfjnaksjdfnkasjfnorw"), 0, 6);
echo $thing . "<br>";
mark("SHA256");


function cleanup(){
    Database::connect()->query("DELETE FROM Accounts");
}
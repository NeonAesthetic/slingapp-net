<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 11/25/16
 * Time: 7:23 PM
 * Test Name: UserID Generation Testing
 *
 */
require_once "classes/Database.php";

$email = "test_email@gmail.com";

mark();

$statement = Database::connect()->prepare("INSERT INTO Snowflake (ID) VALUES(:id)");


for ($i = 0; $i<100000; $i++){
    $v = Database::getFlakeID();
//    echo $v . "<br>";
    $statement->execute([
        ":id"=>$v
    ]);
}

mark("Generate 1000 IDs");

mark();
$statement = Database::connect()->prepare("SELECT * FROM Snowflake ORDER BY ID LIMIT 100");
$statement->execute();
$values = $statement->fetchAll();
mark("Select 100 ids");



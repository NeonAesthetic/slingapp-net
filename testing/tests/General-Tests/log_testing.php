<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 11/6/2016
 * Time: 2:24 PM
 * Test Name: Log Test
 * Description: Logs an action and fetches the log from the database.
 */

require_once "interfaces/DatabaseObject.php";
require_once "classes/Database.php";

DatabaseObject::Log(__FILE__, "Testing", "Test Description");

$stmt = Database::connect()->query("SELECT * FROM Logs WHERE Action='Testing'");
if(!$stmt) var_dump(Database::connect()->errorInfo());
$results = $stmt->execute();
$results = $stmt->fetch(PDO::FETCH_ASSOC);
foreach ($results as $k=>$v){
    echo $k . ": ". $v. "<br>";
}

function cleanup(){
    try{
        Database::connect()->exec("DELETE FROM Logs WHERE Action = 'Testing'");
    }catch(Exception $e){

    }
}
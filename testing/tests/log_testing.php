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

DatabaseObject::Log("Testing", "Test Description");

$stmt = Database::connect()->query("SELECT * FROM logs WHERE Action='Testing'");
$results = $stmt->execute();
$results = $stmt->fetch(PDO::FETCH_ASSOC);
foreach ($results as $k=>$v){
    echo $k . ": ". $v. "<br>";
}
cleanup();

function cleanup(){
    Database::connect()->exec("DELETE FROM logs WHERE Action = 'Testing'");
}
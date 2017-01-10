<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 11/6/2016
 * Time: 2:24 PM
 * Test Name: Log Test
 * Description: Logs an action and fetches the log from the database.
 */

require_once "classes/logging/Logger.php";
mark();
Logger::Log(__FILE__, SLN_ACCESSED_FILE, NULL, NULL, "Test Description", "123.456.789.0");
mark("Log one item");
$stmt = Database::connect()->query("SELECT * FROM Logs WHERE File='log_testing.php'");
if(!$stmt) var_dump(Database::connect()->errorInfo());
$results = $stmt->execute();
$results = $stmt->fetch(PDO::FETCH_ASSOC);
if($results){
    foreach ($results as $k=>$v){
        echo $k . ": ". $v. "<br>";
    }
}


function cleanup(){
    try{
        Database::connect()->exec("DELETE FROM Logs WHERE File = 'log_testing.php'");
    }catch(Exception $e){

    }
}
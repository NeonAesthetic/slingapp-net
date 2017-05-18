<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/24/16
 * Time: 1:46 PM
 */

//testname: Insert and Delete Room Name
//testdesc: This test will insert and delete a room name


require_once "Database.php";

$db = Database::connect();  //get singleton database connection

$db->exec("INSERT INTO Rooms (RoomName) VALUES('Test Room')");  //execute INSERT statement


$results = $db->query("SELECT RoomName FROM Rooms WHERE RoomName='Test Room';");      //Sorta-Prepare a SELECT statement

//assert that the fetched row is equal to "Test Room"
assert($results->fetch(PDO::FETCH_ASSOC)["RoomName"] == "Test Room", "Assert that the room was inserted correctly");


//cleanup function that is called by the test environment
//this will always run, but is outside the scope of error handlers
//if stuff goes wrong it will break
function cleanup(){
    $db = Database::connect()->exec("DELETE FROM Rooms WHERE RoomName = 'Test Room';");
}




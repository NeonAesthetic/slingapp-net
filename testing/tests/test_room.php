<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/27/16
 * Time: 10:29 AM
 */

//testname: Create Room Through Room Class
//testdesc: Create a non-existant room and alter its state with Room methods.

require_once "classes/Room.php";
require_once "classes/Participant.php";
require_once "classes/dummy/DummyParticipant.php";
$room;
$was_exception = false;
try{
    $room = new Room("Test-Room");
}catch (Exception $e){
    $was_exception = true;
}

assert($was_exception === true, "Assert that the Room threw an exception");

$room = Room::createRoom("Test-Room");
$roomid = $room->getRoomID();

$particpantID = $room->addParticipant("Test1");
$particpantID = $room->addParticipant("Test2");

$room_code = $room->addRoomCode($particpantID);
echo "<br><br>Room Code: " . $room_code . "<br>";
$room = new Room($room_code);
echo $room->getJSON();

//$room->delete();


//
//function cleanup(){
//    try{
//        $room = new Room($GLOBALS["RoomCode"]);
//        $room->delete();
//    }catch (Exception $e){
//
//    }
//}
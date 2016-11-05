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

$participant = Participant::createParticipant($roomid, "Test Participant");




function cleanup(){
    try{
        $room = new Room("Test-Room");
        $room->deleteRoom();
    }catch (Exception $e){

    }
}
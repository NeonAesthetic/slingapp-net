<?php
/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 11/10/16
 * Time: 10:04 AM
 * Test Name: Test Room Codes
 * Description: Ensures room codes are working properly
 */

require_once "classes/RoomCode.php";
require_once "classes/Room.php";

try{
    $room_code = new RoomCode("Test-RoomCode");
}catch (Exception $e){
    $was_exception = true;
}

assert($was_exception === true, "Assert that the RoomCode threw an exception");

$room = Room::createRoom("Test-Room");

try {
    $room = RoomCode::createRoomCode("Test-Room", "Ryan", 5, 5);
}catch (Exception $e){
    $was_exception = true;
}

assert($was_exception === true, "Assert that RomeCode thre an exception");

$room_code->delete();
$was_exception = false;
try{
    $room_code = new RoomCode(RoomCode::generate_code(), "Test-Room", "Ryan", 5, 5 );
}catch(Exception $e){
    $was_exception = true;
}

assert($was_exception == true, "Assert that room code was deleted");
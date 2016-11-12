<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/27/16
 * Time: 10:29 AM
 */

//Test Name: Create Room Through Room Class
//Description: Create a non-existant room and alter its state with Room methods.

require_once "classes/Room.php";
#require_once "classes/Participant.php";
#require_once "classes/dummy/DummyParticipant.php";
#$room;
$was_exception = false;

try{
    $room = new Room("Test-Room");
}catch (Exception $e){
    $was_exception = true;
}

assert($was_exception === true, "Assert that the Room threw an exception");

$room = Room::createRoom("Test-Room");
$roomid = $room->getRoomID();

echo "print me";
#$GLOBALS["RoomID"] = 1234;

echo "RoomID:";
#var_dump($GLOBALS['RoomID']);
//$particpantID = $room->addParticipant("Test1", "screenName1");
//$particpantID = $room->addParticipant("Test2", "screenName2");
//
//$room_code = $room->addRoomCode($particpantID);
//$room = new Room($room_code);
//$json = $room->getJSON();
//$object = json_decode($json, true);
//
//assert($object["Type"] == "Room", "Assert that object has correct type attribute");
//assert($object["Participants"][0]["type"] == "ParticipantObject", "Assert that object has correct Participants attribute");
//assert($object["RoomCodes"][0]["code"] == $room_code, "Assert that object has correct RoomCode attribute");
//
//
//$room->delete();
//$was_exception = false;
//try{
//    $room = new Room($room_code);
//}catch(Exception $e){
//    $was_exception = true;
//}
//
//assert($was_exception == true, "Assert that room was deleted");


function cleanup(){
//    try{
//        #echo "vardump: ";
//        #var_dump($GLOBALS['RoomID']);
//        $roomid = $GLOBALS["RoomID"];
//        $sql = "DELETE FROM roomcodes WHERE RoomID = :roomid";
//        $statement = Database::connect()->prepare($sql);
//        $statement->execute([
//            ":roomid"=>$roomid
//        ]);
//
//        $sql = "DELETE FROM participants WHERE RoomID = :roomid";
//        $statement = Database::connect()->prepare($sql);
//        $statement->execute([
//            ":roomid"=>$roomid
//        ]);
//
//        $sql = "DELETE FROM rooms WHERE RoomID = :roomid";
//        $statement = Database::connect()->prepare($sql);
//        $statement->execute([
//            ":roomid"=>$roomid
//        ]);
//    }catch (Exception $e){
//
//    }
}
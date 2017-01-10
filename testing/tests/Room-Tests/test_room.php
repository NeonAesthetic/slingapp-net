<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/27/16
 * Time: 10:29 AM
 * Test Name: Test Room Functionality
 */

//Test Name: Create Room Through Room Class
//Description: Create a non-existant room and alter its state with Room methods.

require_once "classes/Room.php";
$was_exception = false;

try {
    $room = Room::createRoom("room");
} catch (Exception $e) {
    $was_exception = true;
}

assert($was_exception === false, "Assert that the Room didnt threw an exception");

$room = Room::createRoom("Test-Room");
$roomid = $room->getRoomID();

$GLOBALS["RoomID"] = 1234;

$account = Account::CreateAccount("roomtest@test.com", "Bob", "Marley", "password");
$room = Room::createRoom("roomName");
$room->addParticipant($account, "host");
$room->addRoomCode($account->getAccountID());
$account1 = Account::CreateAccount();
$account2 = Account::CreateAccount();
//
$json = $room->getJSON(true);

//foreach($json as $row) {
//    var_dump($row);
//}
//$object = json_decode($json, true);
$roomCode = $room->getRoomCodes()[0]->getCode();

assert($json["Type"] == "Room", "Assert that object has correct type attribute");
assert($json["Accounts"][0]["Type"] == "Account-Tests", "Assert that object has correct Participants attribute");
assert($json["RoomCodes"][0]["Code"] == $roomCode, "Assert that object has correct RoomCode attribute");

$room->delete();

$was_exception = false;
try{
    $room = new Room($roomCode);
}catch(Exception $e){
    $was_exception = true;
}

assert($was_exception == true, "Assert that room was deleted");


function cleanup()
{
    try {
        $sql = "SELECT r.RoomID, a.AccountID
                FROM Accounts AS a
                LEFT JOIN RoomAccount AS ra
                    ON a.AccountID = ra.AccountID
                LEFT JOIN Rooms AS r
                    ON ra.RoomID = r.RoomID
                WHERE (Email = 'testemail@test.com')
                OR (Email = 'email@test.com')
                OR (Email = 'replace@test.com')
                OR (Email = 'testnewemail@test.com')
                OR (Email = 'newer@gmail.com')
                OR (Email = 'roomtest@test.com')
                OR (Email IS NULL)";
        $statement = Database::connect()->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $row) {
            if ($row['RoomID'] != null) {
                $sql = "DELETE
                FROM RoomCodes
                WHERE RoomID = :roomID";
                Database::connect()->prepare($sql)->execute(array(':roomID' => $row['RoomID']));
                $sql = "DELETE
                FROM RoomAccount
                WHERE RoomID = :roomID";
                Database::connect()->prepare($sql)->execute(array(':roomID' => $row['RoomID']));
                $sql = "DELETE
                FROM Rooms
                WHERE RoomID = :roomID";
                Database::connect()->prepare($sql)->execute(array(':roomID' => $row['RoomID']));
            }
            $sql = "DELETE
                FROM Accounts
                WHERE AccountID = :accountID";
            Database::connect()->prepare($sql)->execute(array(':accountID' => $row['AccountID']));
        }
    } catch
    (Exception $e) {
    }
}
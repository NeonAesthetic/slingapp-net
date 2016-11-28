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


//Test for generating a room code
//$account = Account::CreateAccount("email@test.com", "Ryan", "Polasky", "pass");
//$room = Room::createRoom("Test-Room");
//$room->addParticipant($account, "host");
//$room->addRoomCode($account->getAccountID());
//$codes = $room->getRoomCodes();
//assert($codes[0]->getCode() != null, "Code generated correctly");
//
//// Test delete room code
//assert($codes[0]->delete() == true, "Code was deleted");
//
//cleanup();
// //Test expiration date and uses
//$account = Account::CreateAccount("email@test.com", "Ryan", "Polasky", "pass");
//
//#$date = gmdate("Y-m-d H:i:s");
//#echo "date: $date";
//$date = gmdate("Y-m-d H:i:s", strtotime("+10 seconds"));
//#echo "<br>date: $date";
//
//$room = Room::createRoom("Test-Room");
//$room->addParticipant($account, "host");
//$room->addRoomCode($account->getAccountID(), 5, $date);
//
//$codes = $room->getRoomCodes();
//assert($codes[0]->getCode() != null, "Code generated correctly");
//cleanup();

$date = gmdate("Y-m-d H:i:s", strtotime("+10 seconds"));

//Create 5 uses, should fail by inserting 6
$account1 = Account::CreateAccount("testemail@test.com", "first", "last", "pass");
$account2 = Account::CreateAccount("email@test.com", "first", "last", "pass");
$account3 = Account::CreateAccount("replace@test.com", "first", "last", "pass");
$account4 = Account::CreateAccount("testnewemail@test.com", "first", "last", "pass");
$account5 = Account::CreateAccount("roomtest@test.com", "first", "last", "pass");
$account6 = Account::CreateAccount("roomtest@gmail.com", "first", "last", "pass");
$account7 = Account::CreateAccount("roomtest2@gmail.com", "first", "last", "pass");
$room = Room::createRoom("Too Many Accounts");
$room->addParticipant($account1, "host");
$room->addRoomCode($account1->getAccountID(), 5, $date);
$room->addParticipant($account2, "part1");
$room->addParticipant($account3, "part2");
$room->addParticipant($account4, "part3");
$temp1 = $room->addParticipant($account5, "part4");
$temp = $room->addParticipant($account6, "part5");
$tooMany = $room->addParticipant($account7, "part6");

//how to check for number of uses left?
assert($tooMany === false, "Tried adding a 6th participant with only 5 uses available in the room");

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
                OR (Email = 'roomtest@gmail.com')
                OR (Email = 'roomtest@test.com')
                OR (Email = 'roomtest2@gmail.com')
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
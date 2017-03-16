<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 1/11/2017
 * Time: 8:21 PM
<<<<<<< HEAD
 * Test Name: Test DownloadingUploading Files
 * Test Description: ensures files can be sent and recieved from the database
=======
 * Test Name: Test Downloading/Uploading Files
 * Description: ensures files can be sent and recieved from the database
>>>>>>> working
 */

require_once "classes/Room.php";
require_once "classes/Chat.php";
require_once "classes/Message.php";


$file = File::Insert("../tests/uploads/14948841493111845106/test.txt", "test.txt");

function cleanup(){
    try{
        require_once "classes/Database.php";
        try {
            $sql = "SELECT r.RoomID, a.AccountID
                FROM Accounts AS a
                LEFT JOIN RoomAccount AS ra
                    ON a.AccountID = ra.AccountID
                LEFT JOIN Rooms AS r
                    ON ra.RoomID = r.RoomID
                WHERE (Email = 'testemail@test.com')";
            $statement = Database::connect()->prepare($sql);
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

            Database::connect()->query("DELETE
                                        FROM Files
                                        WHERE (Filename = 'test.txt')");

            foreach ($result as $row) {
                if ($row['RoomID'] != null) {
                    $sql = "DELETE
                FROM messages
                WHERE RoomID = :roomID";
                    Database::connect()->prepare($sql)->execute(array(':roomID' => $row['RoomID']));
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
    }catch (Exception $e){}
}
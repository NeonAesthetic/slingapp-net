<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/16/16
 * Time: 5:53 PM
 */
require "Database.php";

class Room
{
    private $_room_codes = [];
    private $_room_id;
    private $_room_name;
    public function __construct($room_name = null, $room_code = null)
    {
        if($room_code !== null){
            $sql = "SELECT * FROM Rooms
                    JOIN Participants
                    ON Rooms.RoomID = Participants.RoomID
                    WHERE Rooms.RoomID = (SELECT RoomID 
                                          FROM RoomCodes 
                                          WHERE RoomCode = :roomcode
                                          )";
            $statement = Database::connect()->prepare($sql);
            $statement->execute([":roomcode" => $room_code]);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        }else if ($room_name != null){
            $sql = "SELECT * FROM Rooms
                    WHERE Rooms.RoomName = :roomname";
            $statement = Database::connect()->prepare($sql);
            $statement->execute([":roomname" => $room_name]);
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            $this->_room_id = $result["RoomID"];
        }
    }

    public static function createRoom($room_name){
        $sql = "INSERT INTO Rooms (RoomName) VALUES (:name)";
        $statement = Database::connect()->prepare($sql);
        if(!$statement->execute([":name" => $room_name])){
            throw new Exception("Could not create room");
        }
        return new Room($room_name);
    }
    
    public function createNewRoomCode(Participant $creator){
        $this->_room_codes[] = 
    }

    public function getRoomID(){
        return $this->_room_id;
    }

    /**
     * @return mixed
     */
    public function getRoomName()
    {
        return $this->_room_name;
    }
    
    public function deleteRoom(){
        $sql = "DELETE FROM Rooms WHERE RoomID = :id";
        $statement = Database::connect()->prepare($sql);
        $statement->execute([":id" => $this->_room_id]);
    }

    


    
}
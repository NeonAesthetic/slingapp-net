<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/16/16
 * Time: 5:53 PM
 */
require "classes/Database.php";
require_once "interfaces/DatabaseObject.php";

class Room extends Database
{
    private $_room_codes = [];
    private $_room_id;
    private $_room_name;
    public function __construct($room_code = null, $room_id = null)
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
            if($result != false){
                $this->_room_id = $result["RoomID"];
            }else{
                throw new Exception("A Room with that code could not be found");
            }

        }else if ($room_id != null){
            $sql = "SELECT * FROM Rooms
                    WHERE RoomID = :roomid";
            $statement = Database::connect()->prepare($sql);
            $statement->execute([":roomid" => $room_id]);
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
        $id = Database::connect()->lastInsertId();
        return new Room(null,$id);
    }
    
    public function createNewRoomCode(Participant $creator){
        
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


    public function delete()
    {
        // TODO: Implement delete() method.
    }

    public function update()
    {
        // TODO: Implement update() method.
    }
}
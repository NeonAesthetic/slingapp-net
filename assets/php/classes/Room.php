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
    private $_room_code;
    private $_db;
    private $_room_id;
    private $_room_name;
    private $_participants;
    public function __construct($room_code)
    {
        $this->_roomCode = $room_code;
        $this->_db = Database::connect();
        if(!$this->get_room_from_database()){
            throw new Exception("Room not in database");
        }
    }

    private function get_room_from_database(){
        $sql = "SELECT * FROM Rooms WHERE RoomID = (SELECT RoomID FROM RoomCodes WHERE RoomCode = :code);";
        $statement = $this->_db->prepare($sql);
        $statement->bindParam(":code", $this->_room_code);
        $results = $statement->fetch(PDO::FETCH_ASSOC);
        if($results){
            $this->_room_id = $results["RoomID"];
            $this->_room_name = $results["RoomName"];
            return true;
        }
        else{
            return false;
        }

    }

    /**
     * @param $room_name
     * @return Room
     */
    public static function createRoom($room_name){
        $room_code = Room::generateRoomCode();
        $sql = "INSERT INTO Rooms VALUES(':room');";
        $statement = Database::connect()->prepare($sql);
        $statement->bindParam(":room", $room_name);
    }

    public static function generateRoomCode(){
        $str = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        return substr(10,8,str_shuffle($str . $str . $str . $str . $str . $str . $str . $str));
    }


    
}
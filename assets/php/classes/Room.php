<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/16/16
 * Time: 5:53 PM
 */
require_once "interfaces/DatabaseObject.php";
require_once "classes/Participant.php";
require_once "classes/RoomCode.php";

class Room extends DatabaseObject
{
    /** @var RoomCode[] $_room_codes **/
    private $_room_codes = [];

    /** @var Participant[] $_participants **/
    private $_participants = [];
    private $_room_id;
    private $_room_name;
    public function __construct($room_code = null, $room_id = null)
    {
        $sql = "";
        $param = "";
        if($room_code != null) {
//            echo "Room Code Lookup on: $room_code <br>";
            $sql = "SELECT DISTINCT * FROM Rooms
                    LEFT JOIN Participants
                    ON Rooms.RoomID = Participants.RoomID
                    LEFT JOIN RoomCodes rc
                    ON rooms.RoomID = rc.RoomID
                    WHERE Rooms.RoomID = (SELECT RoomID 
                                          FROM RoomCodes 
                                          WHERE RoomCode = :param
                                          )";
            $param = $room_code;
        }else if ($room_id !== null) {
//            echo "RoomID lookup: ".$room_id."<br>";
            $sql = "SELECT * FROM Rooms
                    WHERE Rooms.RoomID = :param";
            $param = $room_id;
        }
        $statement = Database::connect()->prepare($sql);
        $statement->execute([":param" => $param]);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
//        var_dump($result);
//        echo

        if($result != false){
            $this->_room_id = $result[0]["RoomID"];
//            echo $this->_room_id . "-------";
            $this->_room_name = $result[0]["RoomName"];
            if($room_code != null){

                foreach ($result as $row) {
                    $this->_participants[] = new Participant($row["ParticipantID"]);
                    if($row["RoomCode"] != null)
                        $this->_room_codes[] = new RoomCode($row["RoomCode"], $row["RoomID"], $row["CreatedBy"]);
                }
                $this->_room_codes = array_unique($this->_room_codes);
                $this->_participants = array_unique($this->_participants);



            }
        }else{
            throw new Exception("A Room with that code could not be found");
        }
    }

    public static function createRoom($room_name){
        $sql = "INSERT INTO Rooms (RoomName) VALUES (:name)";
        $statement = Database::connect()->prepare($sql);
        if(!$statement->execute([":name" => $room_name])){
            throw new Exception("Could not create room");
        }
        $id = Database::connect()->lastInsertId();
//        echo "Last Inserted: " . $id . "<br>";
        return new Room(null,$id);
    }
    
    public function createNewRoomCode(Participant $creator){
        $this->_room_codes[] = new RoomCode($this->_room_id, $creator);
    }

    public function getRoomID(){
        return $this->_room_id;
    }

    public function getRoomName()
    {
        return $this->_room_name;
    }

    public function setRoomName($new_room_name){
        $this->_room_name = $new_room_name;
        $this->_has_changed = true;
    }

    public function addParticipant($fingerprint){
        $new_part = new DummyParticipant($this->_room_id, $fingerprint);
        $this->_participants[] = $new_part;
        return $new_part->getID();
    }
    
    private function deleteRoom(){
        $sql = "DELETE FROM Rooms WHERE RoomID = :id";
        $statement = Database::connect()->prepare($sql);
        $statement->execute([":id" => $this->_room_id]);
    }

    public function delete()
    {
//        echo "ROOM ID: " . $this->_room_id;
        $sql = "DELETE FROM RoomCodes WHERE RoomID=:roomid";
        if(!Database::connect()->prepare($sql)->execute([":roomid"=>$this->_room_id]))
        {
            echo Database::connect()->errorInfo()[2]."<br>";
        }
        $this->_room_codes = [];

        $sql = "DELETE FROM Participants WHERE RoomID=:roomid";
        if(!Database::connect()->prepare($sql)->execute([":roomid"=>$this->_room_id]))
        {
            echo Database::connect()->errorInfo()[2]."<br>";
        }
        $this->_participants = [];

        $sql = "DELETE FROM Rooms WHERE RoomID=:roomid";
        if(!Database::connect()->prepare($sql)->execute([":roomid"=>$this->_room_id]))
        {
            echo Database::connect()->errorInfo()[2]."<br>";
        }
        $this->_room_id = null;
    }

    public function update()
    {
        foreach ($this->_participants as $particpant){
            $particpant->update();
        }
        foreach ($this->_room_codes as $rc){
            $rc->update();
        }
        if($this->hasChanged()) $this->updateRoom();
    }

    private function updateRoom(){
        $sql = "UPDATE Rooms SET RoomName = :roomname WHERE RoomID = $this->_room_id";
        $statement = Database::connect()->prepare($sql);
        $statement->execute([":roomname" => $this->_room_name]);
    }

    public function addRoomCode($creator, $uses = null, $expires = null){
        $new_code = RoomCode::createRoomCode($this->_room_id, $creator, $uses, $expires);
        $this->_room_codes[] = $new_code;
        return $new_code->getCode();
    }
    
    public function getJSON(){
        $json = [];
        $json["Type"] = "Room";
        $json['Participants'] = [];
        foreach($this->_participants as $p){
            $json['Participants'][] = json_decode($p->getJSON(), true);
        }
        $json['RoomCodes'] = [];
        foreach($this->_room_codes as $p){
            $json['RoomCodes'][] = json_decode($p->getJSON(), true);
        }

        $json["RoomID"] = $this->_room_id;
        $json["RoomName"] = $this->_room_name;

        return json_encode($json);
    }
}
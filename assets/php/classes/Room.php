<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/16/16
 * Time: 5:53 PM
 */
set_include_path(realpath($_SERVER["DOCUMENT_ROOT"]) . "/assets/php/");
require_once "interfaces/DatabaseObject.php";
#require_once "classes/Participant.php";
require_once "classes/RoomCode.php";

class Room extends DatabaseObject
{
    /** @var RoomCode[] $_room_codes * */
    private $_room_codes = [];

    /** @var Account[] $_accounts * */
    private $_accounts = [];    //array of participating accounts
    private $_room_id;
    private $_room_name;

    public function __construct($roomID = null, $screenName)
    {
        $roomCodeObject = $this->addRoomCode($screenName);
        $roomCode = $this->_room_codes[] = $roomCodeObject->getCode();

        //what happens when roomID is null?
        if ($roomID != null) {
            echo "Room Code Lookup on: $roomCode <br>";
            $sql = "SELECT DISTINCT * FROM Rooms
                    LEFT JOIN Participants
                    ON Rooms.RoomID = Participants.RoomID
                    LEFT JOIN RoomCodes rc
                    ON rooms.RoomID = rc.RoomID
                    WHERE Rooms.RoomID = (SELECT RoomID
                                          FROM RoomCodes
                                          WHERE RoomCode = :roomCode
                                          )";

        } else if ($roomID !== null) {
//            echo "RoomID lookup: ".$room_id."<br>";
            $sql = "SELECT * FROM Rooms
                    WHERE Rooms.RoomID = :roomCode";
        }
        $statement = Database::connect()->prepare($sql);
        $statement->execute([":roomCode" => $roomCode]);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
//        var_dump($result);
//        echo

        if ($result != false) {
            $this->_room_id = $result[0]["RoomID"];
//            echo $this->_room_id . "-------";
            $this->_room_name = $result[0]["RoomName"];
            if ($roomCode != null) {

                foreach ($result as $row) {
                    $this->_accounts[] = Account::LoginThroughID($row["AccountID"]);
                    if ($row["RoomCode"] != null)
                        $this->_room_codes[] = new RoomCode($row["RoomCode"], $row["RoomID"], $row["CreatedBy"]);
                }
                $this->_room_codes = array_unique($this->_room_codes);
                $this->_accounts[] = array_unique($this->_accounts);
            }
        } else {
            throw new Exception("A Room with that code could not be found");
        }
    }

    public static function createRoom($roomName, $screenName)
    {
        $sql = "INSERT INTO Rooms (RoomName) VALUES (:name)";
        $statement = Database::connect()->prepare($sql);
        if (!$statement->execute([":name" => $roomName])) {
            throw new Exception("Could not create room");
        }
        $id = Database::connect()->lastInsertId();
//        echo "Last Inserted: " . $id . "<br>";
        return new Room($id, $screenName);
    }

//    public static function createNewRoomCode($screenName, $roomID)
//    {        //$creator is participant's
//        $roomCode = $this->addRoomCode($screenName);
//        var_dump($roomCode);
//        $this->_room_codes[] = new RoomCode($roomCode, $roomID, $screenName);
//    }

    public function getRoomID()
    {
        return $this->_room_id;
    }

    public function getRoomName()
    {
        return $this->_room_name;
    }

    public function setRoomName($new_room_name)
    {
        $this->_room_name = $new_room_name;
        $this->_has_changed = true;
    }

    public function addParticipant($token, $screenName)
    {
        $account = Account::Login($token);
        $account->_roomID = $this->getRoomID();
        $account->_screenName = $screenName;
        $this->_accounts[] = $token;
        return $account->getAccountID();
    }

//    public function addParticipant($fingerprint){
//        $new_part = new DummyParticipant($this->_room_id, $fingerprint);
//        $this->_participants[] = $new_part;
//        return $new_part->getID();
//    }

    private function deleteRoom()
    {
        $sql = "DELETE FROM Rooms WHERE RoomID = :id";
        $statement = Database::connect()->prepare($sql);
        $statement->execute([":id" => $this->_room_id]);
    }

    public function delete()
    {
//        echo "ROOM ID: " . $this->_room_id;
        $sql = "DELETE FROM RoomCodes WHERE RoomID=:roomid";
        if (!Database::connect()->prepare($sql)->execute([":roomid" => $this->_room_id])) {
            echo Database::connect()->errorInfo()[2] . "<br>";
        }
        $this->_room_codes = [];

        $sql = "DELETE FROM Participants WHERE RoomID=:roomid";
        if (!Database::connect()->prepare($sql)->execute([":roomid" => $this->_room_id])) {
            echo Database::connect()->errorInfo()[2] . "<br>";
        }
        $this->_accounts = [];

        $sql = "DELETE FROM Rooms WHERE RoomID=:roomid";
        if (!Database::connect()->prepare($sql)->execute([":roomid" => $this->_room_id])) {
            echo Database::connect()->errorInfo()[2] . "<br>";
        }
        $this->_room_id = null;
    }

    public function update()
    {
        foreach ($this->_accounts as $account) {
            $account->update();
        }
        foreach ($this->_room_codes as $rc) {
            $rc->update();
        }
        if ($this->hasChanged()) $this->updateRoom();
    }

    private function updateRoom()
    {
        $sql = "UPDATE Rooms SET RoomName = :roomname WHERE RoomID = $this->_room_id";
        $statement = Database::connect()->prepare($sql);
        $statement->execute([":roomname" => $this->_room_name]);
    }

//    public function addRoomCode($creator, $uses = null, $expires = null)
//    {
//        $new_code = RoomCode::createRoomCode($this->_room_id, $creator, $uses, $expires);
//        $this->_room_codes[] = $new_code;
//        return $new_code->getCode();
//    }

    public function addRoomCode($creator, $uses = null, $expires = null)
    {
        return RoomCode::createRoomCode($this->_room_id, $creator, $uses, $expires);
    }

    public function getJSON($as_array = true)
    {
        $json = [];
        $json["Type"] = "Room";
        $json['Participants'] = [];
//        foreach($this->_participants as $p){
//            $json['Participants'][] = json_decode($p->getJSON(), true);
//        }
        foreach ($this->_accounts as $p) {
            $json['Participants'][] = $p;
        }
        $json['RoomCodes'] = [];
        foreach ($this->_room_codes as $p) {
            $json['RoomCodes'][] = json_decode($p->getJSON(), true);
        }

        $json["RoomID"] = $this->_room_id;
        $json["RoomName"] = $this->_room_name;

        if ($as_array)
            return $json;
        return json_encode($json);
    }
}
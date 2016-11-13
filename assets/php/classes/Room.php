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
require_once "classes/Account.php";

class Room extends DatabaseObject
{
    /** @var RoomCode[] $_room_codes * */
    private $_room_codes = [];

    /** @var Account[] $_accounts * */
    private $_accounts = [];    //array of participating accounts
    private $_room_id;
    private $_room_name;

    public function __construct($roomID, $token, $screenName)
    {
        $this->_room_id = $roomID;
        $accountID = $this->addParticipant($token, $screenName);
        $roomCodeObject = $this->addRoomCode($accountID);
        $roomCode = $this->_room_codes[] = $roomCodeObject->getCode();

        //what happens when roomID is null?
        $sql = "SELECT DISTINCT * FROM Rooms
                    LEFT JOIN Participants
                    ON Rooms.RoomID = Participants.RoomID
                    LEFT JOIN RoomCodes rc
                    ON rooms.RoomID = rc.RoomID
                    WHERE Rooms.RoomID = (SELECT RoomID
                                          FROM RoomCodes
                                          WHERE RoomCode = :roomCode
                                          )";
        $statement = Database::connect()->prepare($sql);
        $statement->execute([":roomCode" => $roomCode]);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($result != false) {
            $this->_room_id = $result[0]["RoomID"];
            $this->_room_name = $result[0]["RoomName"];
            if ($roomCode != null) {

                foreach ($result as $row) {
                    $this->_accounts[] = Account::LoginThroughID($row["AccountID"]);
                    if ($row["RoomCode"] != null)
                        $this->_room_codes[] = new RoomCode($row["RoomCode"], $row["RoomID"], $row["CreatedBy"]);
                }

                #var_dump($this->_accounts);
//                $this->_room_codes = array_unique($this->_room_codes);
//                $this->_accounts[] = array_unique($this->_accounts);
            }
        } else {
            throw new Exception("A Room with that code could not be found");
        }
    }

    /**
     * Function createRoom
     * @param $roomName
     * @param $token
     * @param $screenName
     * @throws Exception
     * @return Room
     */
    public static function createRoom($roomName, $token, $screenName)
    {
        $sql = "INSERT INTO Rooms (RoomName) VALUES (:name)";
        $statement = Database::connect()->prepare($sql);
        if (!$statement->execute([":name" => $roomName])) {
            throw new Exception("Could not create room");
        }
        $id = Database::connect()->lastInsertId();

        return new Room($id, $token, $screenName);
    }

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

    /**
     * Function Login
     * @param $token
     * @param $screenName
     * @return integer
     */
    public function addParticipant($token, $screenName)
    {
        $account = Account::Login($token);
        $account->addParticipant($this->getRoomID(), $screenName);
        $this->_accounts[] = $account;
        return $account->getAccountID();
    }

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

    public function addRoomCode($accountID, $uses = null, $expires = null)
    {
        return RoomCode::createRoomCode($this->_room_id, $accountID, $uses, $expires);
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
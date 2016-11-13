<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/16/16
 * Time: 5:53 PM
 */
set_include_path(realpath($_SERVER["DOCUMENT_ROOT"]) . "/assets/php/");
require_once "interfaces/DatabaseObject.php";
require_once "classes/RoomCode.php";
require_once "classes/Account.php";

class Room extends DatabaseObject
{
    /** @var RoomCode[] $_room_codes * */
    private $_room_codes = [];

    /** @var Account[] $_accounts * */
    private $_accounts = [];    //array of participating accounts
    private $_roomID;
    private $_roomName;

    //pass account object to constructor or just the needed parameters to factor out the select statement
    public function __construct($roomID, $token, $screenName)
    {
        $this->_roomID = $roomID;
        $participantID = $this->addParticipant($token, $screenName);

        $roomCodeObject = $this->addRoomCode($participantID);
        $roomCode = $this->_room_codes[] = $roomCodeObject->getCode();

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
            $this->_roomID = $result[0]["RoomID"];
            $this->_roomName = $result[0]["RoomName"];
            if ($roomCode != null) {

                foreach ($result as $row) {
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
        $roomID = Database::connect()->lastInsertId();

        return new Room($roomID, $token, $screenName);
    }

    public static function createRoomWithoutAccount($roomName, $screenName)
    {
        $sql = "INSERT INTO Rooms (RoomName) VALUES (:name)";
        $statement = Database::connect()->prepare($sql);
        if (!$statement->execute([":name" => $roomName])) {
            throw new Exception("Could not create room");
        }
        $roomID = Database::connect()->lastInsertId();
        $account = Account::createTempAccount($roomID, $screenName);
        $token = $account->getToken();

        return new Room($roomID, $token, $screenName);
    }

    public function getRoomID()
    {
        return $this->_roomID;
    }

    public function getRoomName()
    {
        return $this->_roomName;
    }

    public function setRoomName($newRoomName)
    {
        $this->_roomName = $newRoomName;
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
//        echo "INSIDE ADDPARTICIPANT";
        $account = Account::Login($token);
        $account->addParticipant($this->getRoomID(), $screenName);
        $this->_accounts[] = $account;
        return $account->getParticipantID();
    }

    private function deleteRoom()
    {
        $sql = "DELETE FROM Rooms WHERE RoomID = :id";
        $statement = Database::connect()->prepare($sql);
        $statement->execute([":id" => $this->_roomID]);
    }

    public function delete()
    {
//        echo "ROOM ID: " . $this->_roomID;
        $sql = "DELETE FROM RoomCodes WHERE RoomID=:roomid";
        if (!Database::connect()->prepare($sql)->execute([":roomid" => $this->_roomID])) {
            echo Database::connect()->errorInfo()[2] . "<br>";
        }
        $this->_room_codes = [];

        $sql = "DELETE FROM Participants WHERE RoomID=:roomid";
        if (!Database::connect()->prepare($sql)->execute([":roomid" => $this->_roomID])) {
            echo Database::connect()->errorInfo()[2] . "<br>";
        }
        $this->_accounts = [];

        $sql = "DELETE FROM Rooms WHERE RoomID=:roomid";
        if (!Database::connect()->prepare($sql)->execute([":roomid" => $this->_roomID])) {
            echo Database::connect()->errorInfo()[2] . "<br>";
        }
        $this->_roomID = null;
    }

    /**
     * Function deleteParticipant
     * @param $accountID
     * @return boolean
     * This function will remove the account's participant from the database.
     * This function uses an SQL statement in order to find an
     * existing participant based on an account's ID. If it succeeds in finding
     * and deleting the participant, it will return 'true'.
     * This function should be called when a room expires.
     */
    public function deleteParticipant($accountID)
    {
        $retval = false;

        $sql = "SELECT p.RoomID
                FROM Participants AS p
                  JOIN RoomCodes AS rc
                    ON p.RoomID = rc.RoomID
                WHERE AccountID = :accountID";
        $statement = Database::connect()->prepare($sql);
        $statement->execute(array(':accountID' => $accountID));
        if ($result = $statement->fetch(PDO::FETCH_ASSOC)) {

//            echo "ACCOUNT ID:::$$$$$$$$$$$$:::: ";
//            var_dump($result);
            $sql = "DELETE 
                    FROM RoomCodes
                    WHERE RoomID = :roomID";
            $statement = Database::connect()->prepare($sql);
            if ($statement->execute(array(':roomID' => $result['RoomID']))) {

                $sql = "DELETE 
                    FROM Participants
                    WHERE AccountID = :accountID";

//                echo "AccountID::: $accountID";
                if ($retval = Database::connect()->prepare($sql)->execute(array(':accountID' => $accountID))) {
                    foreach ($this->_accounts as $a) {
                        if ($a->getAccountID() == $accountID) {
                            $a->_roomID = null;
                            $a->_screenName = null;
                        }
                    }
                }
            }
        }
        return $retval;
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
        $sql = "UPDATE Rooms SET RoomName = :roomname WHERE RoomID = $this->_roomID";
        $statement = Database::connect()->prepare($sql);
        $statement->execute([":roomname" => $this->_roomName]);
    }

    public function addRoomCode($participantID, $uses = null, $expires = null)
    {
        return RoomCode::createRoomCode($this->_roomID, $participantID, $uses, $expires);
    }

    public function getAccounts()
    {
        return $this->_accounts;
    }

//    public function getParticipants(){
//
//        foreach ($this->_accounts as $p) {
//            $participants[] = $p[0];
//        }
//
//        var_dump($participants);
//    }
    public function getParticipants()
    {
        $participants = null;
        foreach ($this->_accounts as $p) {
            $participants[] = $p->getScreenName();
        }
//        var_dump($participants);
        return $participants;
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

        $json["RoomID"] = $this->_roomID;
        $json["RoomName"] = $this->_roomName;

        if ($as_array)
            return $json;
        return json_encode($json);
    }
}
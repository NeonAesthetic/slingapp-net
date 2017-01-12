<?php

/**
 * Room Class
 * Created by PhpStorm.
 * User: ian
 * Date: 10/16/16
 * Time: 5:53 PM
 */
require_once "interfaces/DatabaseObject.php";
require_once "classes/RoomCode.php";
require_once "Account.php";
require_once "classes/Chat.php";

//Needs setter for number of rooms code uses
//Needs update screen name function
/**
 * This Class handles all Rooms in the application, and manages their creation
 * and deletion as well as adding and removing participants through its function
 * calls. This class has the ability to make rooms for temporary accounts as well
 * as permanent accounts. The participants generated in each rooms will exist for the
 * duration of the rooms, and can be rejoined by an account via the cookie unique
 * identity that each account will have based on its computer  and browser.
 */
class Room extends DatabaseObject
{
    /** @var RoomCode[] */
    private $_room_codes = [];
    /** @var Account[] */
    private $_accounts = [];
    private $_roomID;
    private $_roomName;
    private $_usesLeft;
    private $_expirationDate;
    private $_chat;
    //pass account object to constructor or just the needed parameters to factor out the select statement
    /**
     * Function Constructor
     * Room constructor.
     * @param $roomID
     * @throws Exception
     * This constructor will allow a rooms to be generated based on the creating participants
     * token, and given screen name, the roomID will act as a unique identifier for the rooms.
     * No rooms can exist without a participant.
     */
    public function __construct($roomID)
    {
        $this->_roomID = $roomID;
        $this->_chat = new Chat($roomID);

        $sql = "SELECT * FROM Rooms
                LEFT JOIN RoomAccount ra
                ON Rooms.RoomID = ra.RoomID
                LEFT JOIN Accounts ac
                ON ac.AccountID = ra.AccountID
                LEFT JOIN RoomCodes rc
                ON Rooms.RoomID = rc.RoomID
                WHERE Rooms.RoomID = :roomid";
        $statement = Database::connect()->prepare($sql);
        $statement->execute([":roomid" => $roomID]);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($result != false) {
            $this->_roomName = $result[0]["RoomName"];

            foreach ($result as $row) {
                if ($row["RoomCode"] != null)
                    $this->_room_codes[$row["RoomCode"]] = new RoomCode($row["RoomCode"], $row["RoomID"], $row["CreatedBy"]);
                if ($row["AccountID"] != null) {
//                    $this->_accounts[$row["AccountID"]] = Account::Login($row["LoginToken"]);
                    /**
                     *          THIS WORKS, PLS NO TOUCH
                     */
                    $this->_accounts[$row["AccountID"]] = new Account(
                                                                        $row["AccountID"],
                                                                        $row["LoginToken"],
                                                                        $row["TokenGenTime"],
                                                                        $row["Email"],
                                                                        $row["FirstName"],
                                                                        $row["LastName"],
                                                                        $row["LastLogin"],
                                                                        $row["JoinDate"],
                                                                        $row["RoomID"],
                                                                        $row["ScreenName"],
                                                                        $row["Active"]
                                                                    );

//                    $this->_accounts[$row["AccountID"]]->_roomID = $roomID;
//                    $this->_accounts[$row["AccountID"]]->setParticipantID($row["ParticipantID"]);
//                    $this->_accounts[$row["AccountID"]]->_screenName = $row["ScreenName"];
                }

//                $this->_accounts = array_unique($this->_accounts);
            }

        } else {
            throw new Exception("Room lookup failed");
        }
    }

    /**
     * Function createRoom
     * @param $roomName
     * @param $token
     * @param $screenName
     * @throws Exception
     * @return Room
     * This Function will allow a rooms to be generated based on a token from
     * the account creating the rooms. This will allow both Account-Tests Users and
     * Temp Users to join the rooms.
     */
    public static function createRoom($roomName)
    {
        $id = Database::getFlakeID();
        $sql = "INSERT INTO Rooms (RoomID, RoomName) VALUES (:id, :name)";
        $statement = Database::connect()->prepare($sql);
        if (!$statement->execute([
            ":id" => $id,
            ":name" => $roomName
        ])) {
            throw new Exception("Could not create room");
        }

        $room = new Room($id);
        return $room;
    }

    public static function GetFromCode($code)
    {
        $sql = "SELECT RoomID FROM RoomCodes WHERE RoomCode = :rc";
        $statement = Database::connect()->prepare($sql);
        $result = $statement->execute([":rc" => $code]);
        if ($result) {
            $id = $statement->fetch()[0];
            error_log($id);
            return new Room($id);
        } else {
            return false;
        }
    }
    /**
     * Function createRoomWithoutAccount
     * @param $roomName
     * @param $screenName
     * @return Room
     * @throws Exception
     * This Function will allow the generation of a rooms without an account
     * token, and will allow the joining of Account-Tests Users as well as Temp Users.
     */

    /**
     * @return mixed
     */
    public function getRoomID()
    {
        return $this->_roomID;
    }

    /**
     * @return mixed
     */
    public function getRoomName()
    {
        return $this->_roomName;
    }

    /**
     * @param $newRoomName
     */
    public function setRoomName($newRoomName)
    {
        $this->_roomName = $newRoomName;
        $this->_has_changed = true;
    }

    public function accountInRoom(Account $account)
    {
        $id = $account->getAccountID();
        $rid = $this->_roomID;
        $sql = "SELECT COUNT(*) 
                FROM RoomAccount
                WHERE RoomID = :rid AND AccountID = :id";
        $statement = Database::connect()->prepare($sql);
        $statement->execute([
            ":id"=>$id,
            ":rid"=>$rid
        ]);
        return $statement->fetch();
    }

    /**
     * Function AddParticipant
     * @param Account $account
     * @param $screenName
     * @return integer
     * This Function allows a participant to be generated in a rooms based
     * on its account token (Temp or Perm) and a screenName that the
     * user provides. Checks how many uses are left and returns false if
     * no uses left.
     */
    public function addParticipant(Account $account, $screenName = null)
    {
        $retval = false;
        if ($screenName == null) {
            $screenName = "Anonymous " . Database::getRandomAnimal();
        }

        if (!array_key_exists($account->getAccountID(), $this->_accounts) && ($this->_usesLeft === null || $this->_usesLeft > 0)) {
            $account->_roomID = $this->_roomID;
            $account->_screenName = $screenName;
            //$account->updateParticipant();
            $this->_accounts[$account->getAccountID()] = $account;

            if($this->_usesLeft != null) {
                $this->_usesLeft--;
            }

            $sql = "INSERT INTO RoomAccount
                    (AccountID, RoomID)
                    VALUES (:acctid, :rmid)";
            $retval = (Database::connect()->prepare($sql)->execute([':acctid' => $account->getAccountID(), ':rmid' => $this->_roomID])) ? true : false;
        }

        return $retval;
    }

    /**
     * Function Delete
     * This function will remove a RoomCode, then all Participants, the the Room
     * that is targeted by it. This will allow referential integrity to remain valid
     * and the removal of the rooms from the active database.
     */
    public function delete()
    {
        $sql = "DELETE FROM RoomCodes WHERE RoomID=:roomid";
        if (!Database::connect()->prepare($sql)->execute([":roomid" => $this->_roomID])) {
            echo Database::connect()->errorInfo()[2] . "<br>";
        }
        $this->_room_codes = [];

        $sql = "UPDATE Accounts AS a
                  JOIN RoomAccount AS ra
                    ON a.AccountID = ra.AccountID
                  JOIN Rooms AS r
                    ON ra.RoomID = r.RoomID
                SET ScreenName = NULL,
                a.Active = 0,
                r.Active = 0
                WHERE r.RoomID = :roomid";

        if (!Database::connect()->prepare($sql)->execute([":roomid" => $this->_roomID])) {
            echo Database::connect()->errorInfo()[2] . "<br>";
        }
        $this->_accounts = [];

        $sql = "DELETE FROM RoomAccount WHERE RoomID=:roomid";
        if (Database::connect()->prepare($sql)->execute([":roomid" => $this->_roomID])) {
            $sql = "DELETE FROM Rooms WHERE RoomID=:roomid";
            Database::connect()->prepare($sql)->execute([":roomid" => $this->_roomID]);
        } else
            echo Database::connect()->errorInfo()[2] . "<br>";

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
     * This function should be called when a rooms expires.
     */
    public function deleteParticipant($accountID)
    {
        $sql = "UPDATE Accounts
                SET ScreenName = NULL,
                Active = 0
                WHERE AccountID = :accid";

        if (Database::connect()->prepare($sql)->execute([":accid" => $accountID])) {
            unset($this->_accounts[$accountID]);
            return true;
        }
        return false;
//        $sql = "DELETE FROM Participants WHERE AccountID = :accid";
//        if(Database::connect()->prepare($sql)->execute([":accid" => $accountID])){
//            unset($this->_accounts[$accountID]);
//            return true;
//        }
//        return false;

//        $sql = "SELECT p.RoomID
//                FROM Participants AS p
//                  JOIN RoomCodes AS rc
//                    ON p.RoomID = rc.RoomID
//                WHERE AccountID = :accountID";
//        $statement = Database::connect()->prepare($sql);
//        $statement->execute(array(':accountID' => $accountID));
//        if ($result = $statement->fetch(PDO::FETCH_ASSOC)) {
//            $sql = "DELETE
//                    FROM RoomCodes
//                    WHERE RoomID = :roomID";
//            $statement = Database::connect()->prepare($sql);
//            if ($statement->execute(array(':roomID' => $result['RoomID']))) {
//
//                $sql = "DELETE
//                    FROM Participants
//                    WHERE AccountID = :accountID";
//
//                if ($retval = Database::connect()->prepare($sql)->execute(array(':accountID' => $accountID))) {
//                    foreach ($this->_accounts as $a) {
//                        if ($a->getAccountID() == $accountID) {
//                            $a->_roomID = null;
//                            $a->_screenName = null;
//                            $a->_active = false;
//                        }
//                    }
//                }
//            }
//        }

//    }
    }

    /**
     * @return mixed
     */
    public function getUsesLeft()
    {
        return $this->_usesLeft;
    }

    public function setParticipantInactive($accountID)
    {
        $retval = false;

        $sql = "SELECT a.RoomID
                FROM Accounts AS a
                  JOIN RoomCodes AS rc
                    ON a.RoomID = rc.RoomID
                WHERE AccountID = :accountID";
        $statement = Database::connect()->prepare($sql);
        $statement->execute(array(':accountID' => $accountID));

        if ($retval = Database::connect()->prepare($sql)->execute(array(':accountID' => $accountID))) {
            foreach ($this->_accounts as $a) {
                if ($a->getAccountID() == $accountID) {
                    $a->_active = false;
                }
            }
        }
        return $retval;
//        $retval = false;
//
//        $sql = "SELECT p.RoomID
//                FROM Participants AS p
//                  JOIN RoomCodes AS rc
//                    ON p.RoomID = rc.RoomID
//                WHERE AccountID = :accountID";
//        $statement = Database::connect()->prepare($sql);
//        $statement->execute(array(':accountID' => $accountID));
//        if ($result = $statement->fetch(PDO::FETCH_ASSOC)) {
//            $sql = "SELECT
//                    FROM RoomCodes
//                    WHERE RoomID = :roomID";
//            $statement = Database::connect()->prepare($sql);
//            if ($statement->execute(array(':roomID' => $result['RoomID']))) {
//
//                $sql = "SELECT *
//                    FROM Participants
//                    WHERE AccountID = :accountID";
//
//                if ($retval = Database::connect()->prepare($sql)->execute(array(':accountID' => $accountID))) {
//                    foreach ($this->_accounts as $a) {
//                        if ($a->getAccountID() == $accountID) {
//                            $a->_active = false;
//                        }
//                    }
//                }
//            }
//        }
//        return $retval;
    }

    /**
     * Function Update
     * This Function allows the update of an account and roomCode based
     * on the changes made in either the account or the roomCode.
     */
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

    /**
     * Function UpdateRoom
     * This Function Changes the currenr rooms name in the database.
     */
    private function updateRoom()
    {
        $sql = "UPDATE Rooms SET RoomName = :roomname WHERE RoomID = $this->_roomID";
        $statement = Database::connect()->prepare($sql);
        $statement->execute([":roomname" => $this->_roomName]);
    }

    /**
     * Function AddRoomCode
     * @param $accountID
     * @param null $uses
     * @param null $expires
     * @return RoomCode
     * This Function adds a roomCode to the given participant, and will allow for
     * specific settings such as the uses remaining for the key as well as the
     * datetime that the key will expire.
     */
    public function addRoomCode($accountID, $uses = null, $expires = null)
    {
        $retval = false;
        if (array_key_exists($accountID, $this->_accounts)) {

            $this->_room_codes[] = $retval = RoomCode::createRoomCode($this->_roomID, $accountID, $uses, $expires);

            if($uses && $uses > $this->_usesLeft) {
                $this->_usesLeft = $uses;
            }
//            $participantID = $this->_accounts[$accountID]->getParticipantID();
//            $this->_room_codes[] = $retval = RoomCode::createRoomCode($this->_roomID, $participantID, $uses, $expires);
        }
        return $retval;
    }

    /**
     * @return array
     */
    public function getAccounts()
    {
        return $this->_accounts;
    }

    /**
     * @return array|null
     */
    public function getParticipants()
    {
        $participants = null;
        foreach ($this->_accounts as $p) {
            $participants[] = $p->getScreenName();
        }
        return $participants;
    }

    /**
     * @return RoomCode[]
     */
    public function getRoomCodes()
    {
        return $this->_room_codes;
    }

    /**
     * Function getJSON
     * @param bool $as_array
     * @return array|string
     * This Function allows the return of the encoded JSON object
     * to be used in different areas of the program.
     */
    public function getJSON($as_array = false)
    {
        $json = [];
        $json["Type"] = "Room";
        $json['Accounts'] = [];
        foreach ($this->_accounts as $a) {
            $json['Accounts'][$a->getAccountID()] = $a->getJSON(true);
            unset($json['Accounts'][$a->getAccountID()]["LoginToken"]);
        }

        $json['RoomCodes'] = [];
        foreach ($this->_room_codes as $p) {
            $json['RoomCodes'][$p->getCode()] = $p->getJSON(true);
        }

        $json["RoomID"] = $this->_roomID;
        $json["RoomName"] = $this->_roomName;

        if ($as_array)
            return $json;
        return json_encode($json);
    }

    public function addMessage($id, $room, $author, $content){
        $this->_chat->addMessage($id, $room, $author, $content);
    }
    
    public function getMessages(){
        $this->_chat->getMessages(500);
        return json_encode($this->_chat->_messages);
    }
}
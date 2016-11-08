<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/27/16
 * Time: 11:43 AM
 */
require_once "interfaces/DatabaseObject.php";

class Participant extends DatabaseObject
{
    protected $_pid;
    protected $_roomid;
    protected $_screenname;
    protected $_room_codes = [];
    protected $_resources = [];
    protected $_finger_print;
    protected $_participant_id;
    protected $_account_id;
    public function __construct($id, $finger_print, $account_id, $room_id){

//        foreach ($results as $row){
//
//        }
//        var_dump($results);

    }

    public static function createFingerPrint(){
        $ip = $_SERVER["REMOTE_ADDR"];
        $userAgent = $_SERVER["HTTP_USER_AGENT"];
        return hash("sha256", $userAgent.$ip);
    }

    public function createParticipant($account_id, $screen_name, $room_code){
        //Creates the participant
        $finger_print = self::createFingerPrint();
        $id = self::getParticipantFromFingerPrint();
        if($id!= null){
            //Need to get room ID, use room code as join?
//            $sql = "SELECT RoomID
//                    FROM RoomCodes
//                    WHERE RoomCode = :roomCode";
//            $statement = Database::connect()->prepare($sql);
//            $statement->execute([":roomID"=>$room_id]);
//            $results = $statement->fetch(PDO::FETCH_ASSOC);


            //Account Exists
            $sql = "INSERT INTO Participants (AccountID, FingerPrint, ParticipantID, RoomID, ScreenName)
            VALUES (:accountID, :fingerPrint,:pid, :roomID, :screenName)";
            $statement = Database::connect()->prepare($sql);
            if(!$statement->execute(array(':accountId' => $this->account_id, ':fingerPrint' => $this->finger_print, ':pid' => $this->id)));
            DatabaseObject::Log("CreateParticipant", "Could Not Insert");
        }


        //Insert the roomid screenid
        //Gets RoomID
        //Gets ScreenName

    }

    public static function getParticipantFromFingerPrint($finger_print)
    {
        $sql = "SELECT PaticipantID FROM Participants WHERE FingerPrint = :fp";
        $statement = Database::connect()->prepare($sql);
        $statement->execute([":fp"=>$finger_print]);
        $results = $statement->fetch(PDO::FETCH_ASSOC);
        if(count($results) > 0){
            //Maybe return entire participant?
            return $results["ParticipantID"];
        }
        else{
            return null;
        }
    }
    

    public function delete()
    {
        $sql = "    DELETE FROM Particpants
                    WHERE ParticipantID = $this->_pid";
        $statement = Database::connect()->prepare($sql);
        $statement->execute();
    }

    public function update()
    {
        $sql = "INSERT INTO Participants (AccountID, FingerPrint, ParticipantID, RoomID, ScreenName)
            VALUES (:accountID, :fingerPrint,:pid, :roomID, :screenName)";
        $statement = Database::connect()->prepare($sql);
        if(!$statement->execute(array(':accountId' => $this->_account_id, ':fingerPrint' => $this->_finger_print, ':pid' => $this->_pid)));
        DatabaseObject::Log("CreateParticipant", "Could Not Insert");
    }

    public function getJSON()
    {
        $json = [];
        $json["type"] = "ParticipantObject";
        $json["ParticpantID"] = $this->_pid;
        return json_encode($json);
    }

    public function getID(){
        return $this->_pid;
    }

    public function __toString()
    {
        return $this->_pid;
    }
}
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

    public static function createParticipant($room_id, $screen_name){
        //Creates the participant
        $finger_print = self::createFingerPrint();
        $id = self::getParticipantFromFingerPrint();
        if($id!= null){
            //Account Exists
//            $sql = "SELECT"
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

            return $results["ParticipantID"];
        }
        else{
            return null;
        }
    }
    

    public function delete()
    {
        // TODO: Implement delete() method.
    }

    public function update()
    {
        // TODO: Implement update() method.
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
<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/27/16
 * Time: 11:43 AM
 */
class Participant extends Database
{
    private $_pid;
    private $_roomid;
    private $_user_name;
    private $_login_token;
    private $_room_codes = [];
    private $_resources = [];
    public function __construct($id = null, $fingerprint = null){
        if($id !== null){
            $sql = "SELECT ParticipantID, RoomID, Username, LoginToken 
                    FROM Participants
                    JOIN Resources 
                    ON Resources.ParticipantID = Participants.ParticipantID
                    JOIN RoomCodes
                    ON Participants.ParticipantID = RoomCodes.CreatedBy
                    WHERE ParticipantID = :param;";
            $param = $id;
        }else if($login_token !== null){

        }else{
            throw new Exception("ID or login_token must be set");
        }

        $statement = Database::connect()->prepare($sql);
        $statement->execute([":param"=> $param]);
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        if($results !== false){
            if (!array_key_exists("ParticipantID", $results))
                $record = $results[0];
            else
                $record = $results;
            $this->_pid = $record["ParticipantID"];
            $this->_roomid = $record["RoomID"];
            $this->_user_name = $record["Username"];
        }
//        foreach ($results as $row){
//
//        }
        var_dump($results);

    }

    public static function createParticipant($room_id, $screen_name){

    }

    public function __get($name)
    {
        $public_get = ["id"];

        if(array_key_exists($name)){
            return $this->$name;
        }
    }

    public function __set($name, $value)
    {
        // TODO: Implement __set() method.
    }

    public function id(){

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
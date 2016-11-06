<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/27/16
 * Time: 11:43 AM
 */
class Participant extends DatabaseObject
{
    protected $_pid;
    protected $_roomid;
    protected $_user_name;
    protected $_login_token;
    protected $_room_codes = [];
    protected $_resources = [];
    public function __construct($id = null, $fingerprint = null){
        $sql = "";
        $param = "";
        if($id != null) {
            $sql = "SELECT p.ParticipantID, p.RoomID, p.ScreenName, p.FingerPrint 
                    FROM Participants p
                    WHERE p.ParticipantID = :param;";
            $param = $id;
        }
        if ($sql !== ""){
            
            $statement = Database::connect()->prepare($sql);
            $statement->execute([":param"=> $param]);
            $results = $statement->fetchAll(PDO::FETCH_ASSOC);
            if($results !== false AND count($results) !== 0){

                if (!array_key_exists("ParticipantID", $results))
                    $record = $results[0];
                else
                    $record = $results;

                $this->_pid = $record["ParticipantID"];
                $this->_roomid = $record["RoomID"];
                $this->_user_name = $record["ScreenName"];
            }

        }
//        foreach ($results as $row){
//
//        }
//        var_dump($results);

    }

    public static function createParticipant($room_id, $screen_name){

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
}
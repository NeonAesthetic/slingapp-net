<?php

/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 11/6/2016
 * Time: 10:06 AM
 */
class DummyParticipant extends Participant
{
    public function __construct($roomid, $screen_name)
    {
        parent::__construct();
        $this->_roomid = $roomid;
        $this->_user_name = $screen_name;
        $sql = "INSERT INTO participants (RoomID, AccountID, ScreenName, FingerPrint) VALUES(:roomid, :acc_id, :screen_name, :fingerprint);";
        $statement = Database::connect()->prepare($sql);
        $statement->execute([   ":roomid"=>$roomid,
                                ":acc_id"=>null,
                                ":screen_name"=>$screen_name,
                                ":fingerprint"=>"TestFingerprint"
                            ]);
        $this->_pid = Database::connect()->lastInsertId();
    }
}
<?php

/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 10/27/16
 * Time: 1:20 PM
 */
class RoomCode extends DatabaseObject
{
    private $_code;
    private $_roomID;
    private $_participantID;
    private $_expire_date;
    private $_uses;

    public function __construct($code, $roomID, $participantID, $uses = null, $expires_in = null)
    {
        $this->_code = $code;
        $this->_roomID = $roomID;
        $this->_participantID = $participantID;
        $this->_uses = $uses;
        $this->_expire_date = $expires_in;
    }

    public static function createRoomCode($roomID, $participantID, $uses = null, $expires_in = null)
    {
        $sql = "    INSERT INTO RoomCodes (RoomCode, RoomID, CreatedBy, ExpirationDate, RemainingUses)
                    VALUES (:code, :rid, :participantID, :exp_date, :rem_uses)";
        $statement = Database::connect()->prepare($sql);
        $code = self::generate_code();

        #echo "Code: ", $code, "<br>", "roomID: ", $roomID, "<br>", "ParticipantID: ", $participantID, "<br>";

        if ($statement->execute([
            ":code" => $code,
            ":rid" => $roomID,
            ":participantID" => $participantID,
            ":exp_date" => $expires_in,
            ":rem_uses" => $uses
        ])
        ) {
            DatabaseObject::Log(__FILE__, "Create", "Participant with ID $participantID created Code $code");
        } else {
            throw new PDOException($statement->errorInfo()[2]);
        }
        return new RoomCode($code, $roomID, $participantID);
    }

    public static function generate_code()
    {
        $chars = str_split("ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");
        $max = count($chars) - 1;
        return $chars[mt_rand(0, $max)] . $chars[mt_rand(0, $max)] . $chars[mt_rand(0, $max)] . $chars[mt_rand(0, $max)] . $chars[mt_rand(0, $max)] . $chars[mt_rand(0, $max)];
    }

    public function delete()
    {
        $sql = "    DELETE FROM RoomCodes
                    WHERE RoomID = $this->_roomID";
        $statement = Database::connect()->prepare($sql);
        $statement->execute();
    }


    public function update()
    {
        $sql = "INSERT INTO RoomCodes (RoomID, CreatedBy, ExpirationDate, RemainingUses)
                    VALUES(':roomID', ':created_by', ':exp', ':rem')";
        $statement = Database::connect()->prepare($sql);
        $statement->execute([":roomID" => $this->_roomID, ":created_by" => $this->_participantID, ":exp" => $this->_expire_date]);
    }

    public function getJSON($as_array = false)
    {
        $json = [];
        $json['type'] = "RoomCode";
        $json["code"] = $this->_code;
        return json_encode($json);
    }

    public function getCode()
    {
        return $this->_code;
    }
}
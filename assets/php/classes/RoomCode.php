<?php

/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 10/27/16
 * Time: 1:20 PM
 */

set_include_path(realpath($_SERVER["DOCUMENT_ROOT"]) . "/assets/php/");
require_once "interfaces/DatabaseObject.php";
require_once "classes/Room.php";

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
        do {
            $sql = "Select RoomCode
                FROM RoomCodes
                WHERE RoomCode = :roomCode";

            $code = self::generate_code();

            $statement = Database::connect()->prepare($sql);
            $statement->execute(array(':roomCode' => $code));
            $result = $statement->fetch(PDO::FETCH_ASSOC);

            if (!$result) {

                $sql = "    INSERT INTO RoomCodes (RoomCode, RoomID, CreatedBy, ExpirationDate, RemainingUses)
                    VALUES (:code, :rid, :createdby, :exp_date, :rem_uses)";
                $statement = Database::connect()->prepare($sql);

                if (!$statement->execute([
                    ":code" => $code,
                    ":rid" => $roomID,
                    ":createdby" => $participantID,
                    ":exp_date" => $expires_in,
                    ":rem_uses" => $uses
                ])
                ) {
                    throw new PDOException($statement->errorInfo()[2]);
                } else {
                    DatabaseObject::Log(__FILE__, "Create", "Participant with ID $participantID created Code $code");
                }
            }
        } while ($result);
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
        if($statement->execute())
        {
            return true;
        }
        else
            return false;
    }

    public function update()
    {
        // Not needed
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->_code;
    }

    public function getJSON($as_array = false)
    {
        $json = [];
        $json['Type'] = "RoomCodes";
        $json["Code"] = $this->_code;

        if ($as_array)
            return $json;
        return json_encode($json);
    }
}
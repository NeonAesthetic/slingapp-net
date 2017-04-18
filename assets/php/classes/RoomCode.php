<?php

/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 10/27/16
 * Time: 1:20 PM
 */

/**
 * This Class handles all Room Codes in the database.
 * This class will create a new Room Code for users that create a new
 * Room.
 *
 * This class uses SQL statements in order to check for duplicate room codes
 * and insert into the database. It will also delete from the database when
 * Room Codes are invalid because of time or remaining uses.
 * */

//set_include_path(realpath($_SERVER["DOCUMENT_ROOT"]) . "/assets/php/");
require_once "interfaces/DatabaseObject.php";
require_once "classes/Room.php";

class RoomCode extends DatabaseObject
{
    private $_code;
    private $_roomID;
    private $_accountID;
    private $_expire_date;
    private $_uses;

    /**
     * RoomCode constructor.
     * @param $code
     * @param $roomID
     * @param $accountID
     * @param null $uses
     * @param null $expires_in
     */
    public function __construct($code, $roomID, $accountID, $uses = null, $expires_in = null)
    {
        $this->_code = $code;
        $this->_roomID = $roomID;
        $this->_accountID = $accountID;
        $this->_uses = $uses;
        $this->_expire_date = $expires_in;
    }

    /**
     * @param $roomID
     * @param $accountID
     * @param null $uses
     * @param null $expires_in
     * @return RoomCode
     * This function is used to create a new room code. It will run an SQL check
     * to make sure the room code that is generated doesnt already exist in the
     * database. If it doesnt the function will then execute SQL to insert the code.
     */
    public static function createRoomCode($roomID, $accountID, $uses = null, $expires_in = null)
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
                    ":createdby" => $accountID,
                    ":exp_date" => $expires_in,
                    ":rem_uses" => $uses
                ])
                ) {
                    return false;
                } else {
                    
                }
            }
        } while ($result);
        return new RoomCode($code, $roomID, $accountID);
    }

    /**
     * @return string
     * This function generates and returns a random 6 digit code.
     */
    public static function generate_code()
    {
        $chars = str_split("ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz0123456789");
        $max = count($chars) - 1;
        return $chars[mt_rand(0, $max)] . $chars[mt_rand(0, $max)] . $chars[mt_rand(0, $max)] . $chars[mt_rand(0, $max)] . $chars[mt_rand(0, $max)] . $chars[mt_rand(0, $max)];
    }

    /**
     * @return bool
     * This function deletes a certain room code from the database.
     */
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

    function __set($name, $value)
    {
        switch (strtolower($name)) {
            case "_uses":
                    $this->_uses = $value;

                break;
            case "_expire_date":
                    $this->_expire_date = $value;
                break;
            default:
        }

        $this->update();
        return $value;
    }



    public function update()
    {
        $sql = "    INSERT INTO RoomCodes (RoomCode, RoomID, CreatedBy, ExpirationDate, RemainingUses)
                    VALUES (:code, :rid, :createdby, :exp_date, :rem_uses)
                    ON DUPLICATE UPDATE (ExpirationDate = :exp_date, RemainingUses = :rem_uses)";
        $statement = Database::connect()->prepare($sql);

        if (!$statement->execute([
            ":code" => $this->_code,
            ":rid" => $this->_roomID,
            ":createdby" => $this->_accountID,
            ":exp_date" => $this->_expire_date,
            ":rem_uses" => $this->_uses
        ])
        );
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->_code;
    }

    public function setUses($uses)
    {
        $this->_uses = $uses;
    }

    /**
     * @param bool $as_array
     * @return array|string
     */
    public function getJSON($as_array = false)
    {
        $json = [];
        $json['Type'] = "RoomCodes";
        $json["Code"] = $this->_code;
        $json["Creator"] = $this->_accountID;
        $json["Expires"] = $this->_expire_date;
        $json["UsesRemaining"] = $this->_uses;
        $json['url'] = "/i/" . $this->_code;

        if ($as_array)
            return $json;
        return json_encode($json);
    }
}
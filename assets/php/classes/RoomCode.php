<?php

/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 10/27/16
 * Time: 1:20 PM
 */
class RoomCode extends Database
{
    private $_code;
    private $_roomID;
    private $_creator;
    
    public function __construct($roomid, $creator, $uses = null, $expires_in = null)
    {
        $this->_code = $this->generate_code();
        $this->_roomid = $roomid;
        $this->_creator = $creator;
        $this->_uses = $uses;
        $this->_expire_date = $expires_in;

        $sql = "    INSERT INTO RoomCodes
                    VALUES ($this->_code, $roomid, $creator, $this->expire_date, $uses)";
        $statement = Database::connect()->prepare($sql);
        $statement->execute();
    }

    public function generate_code(){
        $chars = str_split("ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");
        $max = count($chars) -1;
        return $chars[mt_rand(0,$max)] .$chars[mt_rand(0,$max)] .$chars[mt_rand(0,$max)] .$chars[mt_rand(0,$max)] .$chars[mt_rand(0,$max)] .$chars[mt_rand(0,$max)];
    }

    
    public function delete($code)
    {
        $sql = "INSERT INTO RoomCodes (RoomID, CreatedBy, ExpirationDate, RemainingUses)
                    VALUES(':roomid', ':created_by', ':exp', ':rem')";
        $statement = Database::connect()->prepare($sql);
        $statement->execute([":roomid" => $this->_roomid, ":created_by"=>$this->_creator, ":exp"=>$this->_expire_date]);
    }
}
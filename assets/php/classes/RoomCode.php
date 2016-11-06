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
    private $_roomid;
    private $_creator;
    private $_expire_date;
    private $_uses;
    
    public function __construct($code, $roomid, $creator, $uses = null, $expires_in = null){
        $this->_code = $code;
        $this->_roomid = $roomid;
        $this->_creator = $creator;
        $this->_uses = $uses;
        $this->_expire_date = $expires_in;
    }

    public static function createRoomCode($roomid, $creator, $uses = null, $expires_in = null){
        $sql = "    INSERT INTO RoomCodes (RoomCode, RoomID, CreatedBy, ExpirationDate, RemainingUses)
                    VALUES (:code, :rid, :createdby, :exp_date, :rem_uses)";
        $statement = Database::connect()->prepare($sql);
        $code = self::generate_code();
        if(!$statement->execute([
            ":code"=>$code,
            ":rid" => $roomid,
            ":createdby"=>$creator,
            ":exp_date"=>$expires_in,
            ":rem_uses"=>$uses
        ])){
            throw new PDOException($statement->errorInfo()[2]);
        }
    }

    public static function generate_code(){
        $chars = str_split("ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");
        $max = count($chars) -1;
        return $chars[mt_rand(0,$max)] .$chars[mt_rand(0,$max)] .$chars[mt_rand(0,$max)] .$chars[mt_rand(0,$max)] .$chars[mt_rand(0,$max)] .$chars[mt_rand(0,$max)];
    }

    public function delete()
    {
        $sql = "    DELETE FROM RoomCodes
                    WHERE RoomID = $this->_roomid";
        $statement = Database::connect()->prepare($sql);
        $statement->execute();
    }

    
    public function update()
    {
        $sql = "INSERT INTO RoomCodes (RoomID, CreatedBy, ExpirationDate, RemainingUses)
                    VALUES(':roomid', ':created_by', ':exp', ':rem')";
        $statement = Database::connect()->prepare($sql);
        $statement->execute([":roomid" => $this->_roomid, ":created_by"=>$this->_creator, ":exp"=>$this->_expire_date]);
    }

    public function getJSON()
    {
        $json = [];
        $json['type'] = "RoomCode";
        return json_encode($json);
    }

    public function getCode(){
        return $this->_code;
    }
}
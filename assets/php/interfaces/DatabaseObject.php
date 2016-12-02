<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/27/16
 * Time: 1:24 PM
 */


require_once "classes/Database.php";

const LOG_CACHE_HIT = 0;
const LOG_CACHE_MISS = 1;
const LOG_REGISTER = 2;
const LOG_MESSAGE_SENT = 3;
const LOG_ERROR_ACCOUNT_NOT_FOUND = 4;
const LOG_CREATE_ROOM = 5;
const LOG_CREATE_CODE = 6;
const LOG_CREATE_ACCOUNT = 7;
const LOG_NOT_AUTHENTICATED = 8;
const LOG_NOT_AUTHORIZED = 9;
const LOG_ACCESSED_SOCKET = 10;
const LOG_JOINED_ROOM = 11;
const LOG_ERROR = 12;

abstract class DatabaseObject
{

    protected $_has_changed;
    public function hasChanged(){
        return $this->_has_changed;
    }

    public abstract function delete();
    //deletes object from database

    public abstract function update();

    public abstract function getJSON($as_array = false);

    public static function Log($filename, $type, $description, $user, $room, $ip = false){
        $file = basename($filename);
        if($ip == true)
            $ip = $_SERVER["REMOTE_ADDR"];

        $sql = "INSERT INTO Logs (IP, File, TypeID, Description, AccountID, RoomID) VALUES(:ip, :file, :action, :desc, :user, :room)";
        Database::connect()->prepare($sql)->execute([
            ":ip"=>$ip,
            ":file"=>$file,
            ":action"=>$type,
            ":desc"=>$description,
            ":user" => $user,
            ":room"=> $room
        ]);
    }
    
}
<?php

/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 12/3/2016
 * Time: 11:22 AM
 */
require_once "classes/Database.php";


const SLN_CACHE_HIT = 0;
const SLN_CACHE_MISS = 1;
const SLN_REGISTER = 2;
const SLN_MESSAGE_SENT = 3;
const SLN_ERROR_ACCOUNT_NOT_FOUND = 4;
const SLN_CREATE_ROOM = 5;
const SLN_CREATE_CODE = 6;
const SLN_CREATE_ACCOUNT = 7;
const SLN_NOT_AUTHENTICATED = 8;
const SLN_NOT_AUTHORIZED = 9;
const SLN_ACCESSED_ENDPOINT = 10;
const SLN_JOINED_ROOM = 11;
const SLN_ERROR = 12;
const SLN_ACCESSED_FILE = 13;
const SLN_UPDATE = 14;

const LogText = [
    SLN_CACHE_HIT => "Cache Hit",
    SLN_CACHE_MISS => "Cache Miss",
    SLN_REGISTER => "Register",
    SLN_MESSAGE_SENT => "Message Sent",
    SLN_ERROR_ACCOUNT_NOT_FOUND => "Account Not Found",
    SLN_CREATE_ROOM => "Create Room",
    SLN_CREATE_CODE => "Create Invite Code",
    SLN_CREATE_ACCOUNT => "Create Account",
    SLN_NOT_AUTHENTICATED => "Not Authenticated",
    SLN_NOT_AUTHORIZED => "Not Authorized",
    SLN_ACCESSED_ENDPOINT => "Accessed Endpoint",
    SLN_JOINED_ROOM => "Joined Room",
    SLN_ERROR => "Error",
    SLN_ACCESSED_FILE => "Accessed File",
    SLN_UPDATE => "Update"
];



class Logger
{

    public static function Log($filename, $log_type,  $user, $room, $event_desc, $ip = null){
        try{

            $log_string = "[" . date(DATE_ATOM) . "] " . LogText[$log_type] . ": " . $event_desc . "\n";

            $sql = "INSERT INTO Logs (IP, File, TypeID, Description, AccountID, RoomID) VALUES(:ip, :file, :type, :desc, :user, :room)";
            Database::connect()->prepare($sql)->execute([
                ":ip"=>$ip,
                ":file"=>$filename,
                ":type"=>$log_type,
                ":desc"=>$event_desc,
                ":user" => $user,
                ":room"=> $room
            ]);
        }catch(Throwable $t){
            return "Log Error";
        }

        return $log_string;
    }
}
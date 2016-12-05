<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 11/19/16
 * Time: 12:40 PM
 */
set_include_path("C:\\Users\\Niceg\\slingapp-net\\assets\\php");
require_once "components/StandardHeader.php";
require_once "classes/Room.php";

$p = GetParams("action", "roomname", "screenname", "token", "code", "room");

switch ($p['action']) {
    case "create":
        $room = Room::createRoom($p["roomname"]);
        error_log($p["token"]);
        $account = Account::Login($p["token"]);
        error_log($account->getAccountID());
        $room->addParticipant($account);
        if($room){
            echo $room->getJSON();
        }else{
            echo json_encode(["error"=>"Could not lookup account"]);
        }
        break;
    case "join":
    {
        $code = $p["code"];
        $room = Room::GetFromCode($code);
        if($room){
            $account = Account::Login($p["token"]);
            $room->addParticipant($account);
            echo $room->getJSON();
        }else{
            echo json_encode(false);
        }

    }
    break;

    case "gencode":
    {
        $room = new Room($p["room"]);
        if($room){
            $account = Account::Login($p["token"]);
            $code = $room->addRoomCode($account->getAccountID());
            if($code)
                echo $code->getJSON();
            else{
                var_dump($room);
            }
        }else{
            echo json_encode(false);
        }
    }break;

    case "changename":
    {
        $room = new Room($p["room"]);
        if($room){
            $account = Account::Login($p["token"]);
            $code = $room->addRoomCode($account->getAccountID());
            if($code)
                echo $code->getJSON();
            else{
                var_dump($room);
            }
        }else{
            echo json_encode(false);
        }
    }break;

}
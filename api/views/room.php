<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 3/23/2017
 * Time: 11:26 AM
 */

require_once "classes/http/HTTPResponse.php";
require_once "classes/Room.php";
require_once "classes/Account.php";


function create_room_and_join_account($room_name){
    $response_object = [];
    $token = $_COOKIE['Token'] OR $_POST['Token'];
    $account = Account::Login($token);

    if($account){
        $room_name = urldecode($room_name);
        $room = Room::createRoom($room_name);
        $room->addParticipant($account);
        $response_object['success'] = true;
        $response_object['room'] = format_room_json($room->getJSON(true));

    }else{
        $response_object['success'] = false;
    }
    
    return new HTTPResponse($response_object);
}

function room_action($room_id, $action){
    ob_start();
    echo "Action: " . $action . "<br>";
    echo "Room: " . $room_id . "<br>";
    return ob_get_clean();
}

function room_view($room_id){
    $room = new Room($room_id);
    $json = $room->getJSON(true);


    return new HTTPResponse(format_room_json($json));
}

function format_room_json($json){
    $json['Participants'] = count($json['Accounts']);
    unset($json['Accounts']);
    unset($json['RoomCodes']);
    $json['URL'] = "/rooms/" . $json['RoomID'];
    return $json;
}
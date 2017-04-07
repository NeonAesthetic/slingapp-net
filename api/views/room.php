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


function roomless_action($action){
    switch($act)
    return new HTTPResponse([
        "action"=>$action,
        "api_version"=>"2.0.1"
    ]);
}


function create_room_and_join_account($room_name){
    $response_object = [];
    $token = $_COOKIE['Token'] OR $_POST['Token'];
    $account = Account::Login($token);

    if($account){
        $room_name = urldecode($room_name);
        $room = Room::createRoom($room_name);
        $room->addParticipant($account);
        $response_object['success'] = true;
        $response_object['room'] = $room->getJSON(true);

    }else{
        
    }





    return $room->getRoomID();
}

function room_action($room_id, $action){
    ob_start();
    echo "Action: " . $action . "<br>";
    echo "Room: " . $room_id . "<br>";
    return ob_get_clean();
}
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


function room_view($room_id){
    try{
        $room = new Room($room_id);
        return new HTTPResponse(format_room_json($room->getJSON(true)), 200);
    }catch(Exception $e){
        return new HTTPResponse([
            "error" => $e->getMessage()
        ], 404);
    }




}

function format_room_json($json){
    $json['Participants'] = count($json['Accounts']);
    unset($json['Accounts']);
    unset($json['RoomCodes']);
    $json['URL'] = "/rooms/" . $json['RoomID'];
    return $json;
}

function room_participant_count($room_id){
    $sql = "SELECT COUNT(*) FROM RoomAccount WHERE RoomID = :rid";
    $stmt = Database::connect()->prepare($sql);
    $stmt->execute([":rid" => $room_id]);
    return new HTTPResponse([
        "participants" => $stmt->fetch()[0]
    ]);
}

function join_existing_room($invite_code){
    $response_object = [];
    $token = $_COOKIE['Token'] OR $_POST['Token'];
    $account = Account::Login($token);
    if(!$account){
        return new HTTPResponse(["error"=>"Not authorized"], 401);
    }
    $room = Room::GetFromCode($invite_code);

    if(!$room){
        return new HTTPResponse(["error"=>"Room not found"], 404);
    }
    $room->addParticipant($account);
    return new HTTPResponse([
        "success" => true,
        "room" => format_room_json($room->getJSON(true))
    ], 200);


}

function delete_room($room_id){
    $token = $_COOKIE['Token'] OR $_POST['Token'];
    $account = Account::Login($token);
    if(!$account) return new HTTPResponse(["error" => "Not authorized"], 401);
    try{
        $room = new Room($room_id);
        if($room->getCreatorID() != $account->getAccountID()) return new HTTPResponse(["error" => "Forbidden: you don't have access to this room"], 401);
        $room->delete();
        return new HTTPResponse(["success" => true], 200);
    }catch (Exception $e){
        return new HTTPResponse(["success" => false, "error" => $e->getMessage()], 500);
    }
}

function leave_room($room_id){
    $token = $_COOKIE['Token'] OR $_POST['Token'];
    $account = Account::Login($token);
    if(!$account) return new HTTPResponse(["error" => "Not authorized"], 401);
    try{
        $room = new Room($room_id);
        $room->removeParticipant($account->getAccountID());
        return new HTTPResponse(["success" => true], 200);
    }catch (Exception $e){
        return new HTTPResponse(["success" => false, "error" => $e->getMessage()], 500);
    }
}

function room_create_invite($room_id){
    $token = $_COOKIE['Token'] OR $_POST['Token'];
    $account = Account::Login($token);

    if(!$account){
        return new HTTPResponse(["error"=>"Not authorized"], 401);
    }
    try{
        $room = new Room($room_id);
        $code = $room->addRoomCode($account->getAccountID(), -1);
        return new HTTPResponse([
            "success" => true,
            "code" => $code->getJSON(true)
        ], 200);
    }catch (Exception $e){
        return new HTTPResponse(["success" => false, "error" => $e->getMessage()], 500);
    }
}
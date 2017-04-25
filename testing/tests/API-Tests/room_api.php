<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 4/18/2017
 * Time: 11:00 AM
 * Test Name: Room Endpoint
 * Test Description: This test runs through all the /api/room/* routes to make sure they work correctly.
 */
require_once "classes/Account.php";
require_once "classes/Room.php";


$account = Account::CreateAccount();
$token = $account->getToken();

$stream_context=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
    "http"=>[
        'header'=>'Cookie: Token=' . $token . "\r\n"
    ]
);

const ENDPOINT_ROOM_BASE = "http://localhost/api/room/";

$ROOM_CREATE_NEW    = "new/name";
$ROOM_VIEW_JSON     = ":roomid:";
$ROOM_JOIN          = "join/:roomcode:";
$ROOM_DELETE        = ":roomid:/delete/";
$ROOM_INVITE        = ":roomid:/invite/";


/***
 * Create a new empty Room
 */
mark();
$new_room = json_decode(file_get_contents(ENDPOINT_ROOM_BASE . $ROOM_CREATE_NEW, false, stream_context_create($stream_context)), true);
mark("Create blank Room");
assert($new_room['success'] == true, "Assert that a new room was create successfully.");



/***
 * View Room JSON
 */
$ROOM_VIEW_JSON = $new_room['room']['RoomID'];
mark();
$json = json_decode(file_get_contents(ENDPOINT_ROOM_BASE . $ROOM_VIEW_JSON, false, stream_context_create($stream_context)), true);
mark("Lookup Room");
assert($json['RoomID'] == $new_room['room']['RoomID'], "Assert that room lookup is correct");


/***
 * Create Invite Code
 */
$ROOM_INVITE = $new_room['room']['RoomID'] . "/invite/";
mark();
$json = json_decode(file_get_contents(ENDPOINT_ROOM_BASE . $ROOM_INVITE, false, stream_context_create($stream_context)), true);
mark("Create Invite");
$room_code = $json['code']['Code'];
assert(strlen($room_code) == 6, "Assert that room code is created");


/***
 * Join a Room
 */
$ROOM_JOIN = "join/" . $room_code . "/";
$new_account = $account->CreateAccount();
$token2 = $new_account->getToken();
$stream_context['http']['header'] = "Cookie: Token=" . $token2 . "\r\n";
mark();
$json = json_decode(file_get_contents(ENDPOINT_ROOM_BASE . $ROOM_JOIN, false, stream_context_create($stream_context)), true);
mark("Join Room");
assert($json['RoomID'] == $new_room['room']['RoomID'], "Assert joined correct room");



/***
 * Delete a Room
 */
$ROOM_DELETE = $new_room['room']['RoomID'] . "/delete/";
$stream_context['http']['header'] = "Cookie: Token=" . $token . "\r\n";
mark();
$json = json_decode(file_get_contents(ENDPOINT_ROOM_BASE . $ROOM_DELETE, false, stream_context_create($stream_context)), true);
mark("Delete Room");
assert($json['success'], "Assert deleted room");

try{
    $room = new Room($new_room['room']['RoomID']);
    assert(1==2, "Assert that the above operation fails");
}catch (Exception $e){

}



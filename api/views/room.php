<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 3/23/2017
 * Time: 11:26 AM
 */

require_once "classes/http/HTTPResponse.php";


function no_room_action($action){
    return new HTTPResponse([
        "action"=>$action,
        "api_version"=>"2.0.1"
    ]);
}

function room_action($room_id, $action){
    ob_start();
    echo "Action: " . $action . "<br>";
    echo "Room: " . $room_id . "<br>";
    return ob_get_clean();
}
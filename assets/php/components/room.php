<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 11/19/16
 * Time: 12:40 PM
 */

require_once "components/StandardHeader.php";
require_once "classes/Room.php";

$p = GetParams("action", "roomname", "screenname", "token", "code", "room", "file");

//ini_set('display_errors',1);
//error_reporting(E_ALL);

switch ($p['action']) {
    case "create":
        $room = Room::createRoom($p["roomname"] ? $p["roomname"] : "");

        $account = Account::Login($p["token"]);

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

    case "changeuses":
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

    case "upload": {
        $zip = new ZipArchive();
        $filePath = $_FILES['upload']['tmp_name'];
        $fileName = $_FILES['upload']['name'];
        $alias = randStrGen();
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $p['room'] . "/";
        $urlPath = $_SERVER['SERVER_NAME'] . '/uploads/' . $p['room'] . "/" . $alias;
        $fullPath = $uploadDir . $alias;

        mkdir($uploadDir);
        if(move_uploaded_file($filePath, $fullPath)) {
            if ($zip->open($fullPath . ".zip", ZipArchive::CREATE) === true) {
                $zip->addFile($fullPath, $fileName);
                $zip->close();
                unlink($fullPath);

                $fileJSON = File::Insert($urlPath . ".zip", $fileName);
                echo ($fileJSON) ? $fileJSON : json_encode(false);
        }
        else
            echo json_encode(['zipStatus' => "failed"]);


        } else
            echo json_encode(false);

    }break;
}

function randStrGen() {
    $result = "";
    $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
    $charArray = str_split($chars);
    for($i  = 0; $i < 8; $i++) {
        $alias = array_rand($charArray);
        $result .= "".$charArray[$alias];
    }
    return $result;

}

function retrieveFile($fileid) {

}
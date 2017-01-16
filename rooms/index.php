<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/16/16
 * Time: 5:29 PM
 */

$roomid = $_GET["room"];
require_once realpath($_SERVER['DOCUMENT_ROOT']) . "/assets/php/components/StandardHeader.php";
require_once "classes/Room.php";
$token = $_COOKIE["Token"];
$room = new Room($roomid);
$account = Account::Login($token);

if(!$account){
    header("HTTP/1.1 401 Unauthorized");
    header("Location: /assets/error/401.html");
}

if(!$room->accountInRoom($account))
{
    var_dump($room->getAccounts());
    exit();
}
if($room){
    $room_json = $room->getJSON();
}
else{
//    ApacheError(404);
}
?>
<html>
<head>
    <title>
        Room
    </title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/assets/css/room.css">
</head>

<body style="background-color: #4286f4;">
<div class="module-container">
    <div class="module " id="screen-collection" style="width: 200px; ;">
        <span class="vertical-text">SCREENS</span>
    </div>
    <div class="module" id="controls" style="height: 80px; padding-left: 20px; width: 60%">
        <button class="btn btn-primary" onclick="showSettings()">SETTINGS</button>
        <button style="margin-left: 540px;" class="btn btn-primary" onclick="leaveRoom()">HOME</button>
    </div>
    <div class="module" id="info" style="height: 80px; padding-left: 20px; width: calc(40% - 230px)">
        <span style="font-size: 16px;color: #333">Room Link: <a href="http://<?=$_SERVER['HTTP_HOST']?>/room/<?=$roomid?>">slingapp.net/room/<?=$roomid?></a></span>
    </div>
    <div class="module" id="main" style="height: 60%; padding-left: 20px; width: 60%">
        Main Screen
    </div>
    <div class="module" id="chat" style="height: calc(100% - 100px); padding-left: 20px; width: calc(40% - 230px)">
        <textarea rows="1" class="send-box"></textarea>

        </div>
    </div>



<script type='text/javascript' src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.1.1.min.js"></script>
<script type='text/javascript' src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="/assets/js/sling.js"></script>
<script src="/assets/js/room.js"></script>

<script>
    window.addEventListener("load", function () {
    });
    Account.data = <?=$account->getJSON()?>;
    Room.data = <?=$room_json?>;

</script>


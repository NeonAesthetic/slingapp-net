<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/16/16
 * Time: 5:29 PM
 */

$room = $_GET["room"];
set_include_path(realpath($_SERVER['DOCUMENT_ROOT']) . "/assets/php");
require_once "classes/Room.php";
$token = $_COOKIE["Token"];

$room_obj = new Room($room, $token);
$account = Account::Login($token);

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
        <button class="btn btn-primary" onclick="displayRoomCodes()">Show Room Codes</button>
    </div>
    <div class="module" id="info" style="height: 80px; padding-left: 20px; width: calc(40% - 230px)">
        <span style="font-size: 16px;color: #333">Room Link: <a href="http://<?=$_SERVER['HTTP_HOST']?>/room/<?=$room?>">slingapp.net/room/<?=$room?></a></span>
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
    Account.data = <?=$account->getJSON()?>;
    Room.data = <?=$room_obj->getJSON()?>;
    window.addEventListener("load", function () {
        console.log(Account.data);
        console.log(Room.data);
    })


    
</script>


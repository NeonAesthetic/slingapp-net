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
    echo "Hi:";
    error_log($room->accountInRoom($account));
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

</head>
<body style="background-color: #38474F; overflow: hidden">
<div class="module-container" id="grad1" style="background-color: #38474F; padding-right: 400px; position: absolute">
    <div class="roomMainBack">
        <div class="roomSideBack" id="screensList" style="left: 0">
            <div class="roomSide " id="screen-title" style="height: 35px; text-align: center; background-color: rgba(0,0,0,0)">
                <span class="vertical-text" style="color: white; background-color: rgba(0,0,0,0)">USERS</span>
            </div>
            <div class="roomSide " id="screen-collection" style="background-color: darkcyan">
                <div class="roomSideTitle">
                    <span class="vertical-text">Anonymous Cat</span>
                </div>
            </div>
            <div class="roomSide " id="screen-collection" style="background-color: darkseagreen">
                <div class="roomSideTitle">
                    <span class="vertical-text">Anonymous Dog</span>
                </div>
            </div>
            <div class="roomSide " id="screen-collection" style="background-color: darkturquoise">
                <div class="roomSideTitle">
                    <span class="vertical-text">Anonymous Penguin</span>
                </div>
            </div>
        </div>
        <div class="roomHeaderBack"style="height: 60px; border-radius: 2px;">
            <div class="roomSide" style="height: 50px; ">
                <button class="buttonRoom" style="margin: auto 2% auto 3%; width: 30%;" onclick="showSettings()">SHARE SCREEN</button>
                <div class="roomSideTitle" style="text-align: center; width: 30%; min-width:20%; height: 20px; margin: -25px auto 10px auto; background-color: rgba(0,0,0,0)">
                    <span class="vertical-text" id="r-title">ROOM TITLE</span>
                </div>
                <button class="buttonRoom" style="margin: -30px auto auto 67%; width: 30%; min-width: 50px;" onclick="leaveRoom()">LEAVE ROOM</button>
            </div>
        </div>

        <div class="screen-container">
            <div class="screen" style="">
                <video class="inner" ondblclick="fullScreen(this)">
<!--                <source src="http://clips.vorwaerts-gmbh.de/VfE_html5.mp4" type="video/mp4">-->
                </video>
<!--                <div class="screen-bar" >-->
<!--                    <span class="vertical-text" style="color: white; font-size: small">(You)</span>-->
<!--                </div>-->
            </div>
            <div class="screen" style="">

                <video class="inner" ondblclick="fullScreen(this)">
<!--                    <source src="http://clips.vorwaerts-gmbh.de/VfE_html5.mp4" type="video/mp4">-->
                </video>

<!--                <div class="screen-bar" >-->
<!--                    <span class="vertical-text" style="color: white; font-size: small">(You)</span>-->
<!--                </div>-->
            </div>
        </div>

    </div>

<!---->
<!--    <div class="roomSideBack" id="controls" style="text-align: center; height: 70px;  width: 60%; position: fixed; bottom:0;margin: auto 200px 10px 210px;">-->
<!--        <div class="roomSide" style="height: 50px">-->
<!--            <button class="buttonRoom" onclick="showSettings()">INVITE</button>-->
<!--            <button class="buttonRoom" onclick="showSettings()">SETTINGS</button>-->
<!--            <button class="buttonRoom" onclick="showSettings()">SHARE FILE</button>-->
<!--            <button class="buttonRoom" onclick="showSettings()">LEAVE ROOM</button>-->
<!--        </div>-->
<!--    </div>-->

    
</div>

<div class="panel" id="chat" style="height: 100%; position: absolute; right: 0;width: 400px; max-width:100%;margin: 5px auto auto; background-color: #333333;">
    <div id="chat-log" style="background-color: #333333;"></div>
    <div id="send-box" style="background-color: #333333; ">
        <input id="message-input" onkeypress="if (event.keyCode == 13) sendMessage()" >
        <button onclick="sendMessage()">SEND</button>
        <div id="file-upload">
            <label for="file-input">
                <img src="upload.png">
            </label>
            <input id="file-input" name="upload-file" type="file" onchange="uploadFile(this)"/>
        </div>

    </div>
    <div  style="width: 100%; ">
<!--        <div id="progressNumber" style="margin:0; color:white">-->
<!--            <progress id="prog" value="0" max="100.0"></progress>-->
<!--            0%-->
<!--        </div>-->
        <button class="buttonRoom" style="margin: 5px; width: calc(50% - 10px);" onclick="showSettings()">MUTE</button>
        <button class="buttonRoom" style="margin: 5px; width: calc(50% - 10px);" onclick="connectVoice()">CONNECT VOICE</button>
        <button class="buttonRoom" style="margin: 5px; width: calc(50% - 10px)" onclick="openInvites()">INVITE</button>
        <button class="buttonRoom" style="margin: 5px; width: calc(50% - 10px)" onclick="showSettings()">SETTINGS</button>
    </div>
</div>


<script type='text/javascript' src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.1.1.min.js"></script>
<script type='text/javascript' src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="/assets/js/FileSaver.js"></script>
<script src="/assets/js/sling.js"></script>
<script src="/assets/js/room.js"></script>
<script src="/assets/js/Autolinker.js"></script>

<link rel="stylesheet" href="/assets/css/room.css">
<script>
    window.addEventListener("load", function () {

    });
    Account.data = <?=$account->getJSON()?>;
    Room.data = <?=$room->getJSON()?>;
    Messages = <?=$room->getMessages()?>;

    function fullScreen(element) {
        if (element == document.fullscreenElement){
            element.webkitExitFullscreen();
        }else{
            element.webkitRequestFullscreen();

        }
    }
</script>


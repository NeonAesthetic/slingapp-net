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
<body style="background-color: #38474F; overflow: hidden" onload="newUserSet('small', null)">
<div class="module-container" id="grad1" style="background-color: #38474F; padding-right: 400px; position: absolute">
    <div class="roomMainBack" id="mainBackground">
        <div class="roomSideBack" id="screensList" style="left: 0">
            <div class="roomSide " id="screen-title" style="height: 35px; text-align: center; background-color: #333333">
                <span class="vertical-text" style="color: white; background-color: rgba(0,0,0,0)">USERS</span>
            </div>
        </div>
        <div class="roomHeaderBack" style="height: 60px; border-radius: 2px;">
            <div class="roomSide" style="height: 50px; background-color: transparent">
                <button class="buttonRoom" style="margin: auto 2% auto 3%; width: 30%;" onclick="showSettings()">SHARE SCREEN</button>
                <div class="roomSideTitle" style="text-align: center; width: 30%; min-width:20%; height: 20px; margin: -25px auto 10px auto; background-color: rgba(0,0,0,0)">
                    <span class="vertical-text" id="r-title">ROOM TITLE</span>
                </div>
                <button class="buttonRoom" style="margin: -30px auto auto 67%; width: 30%; min-width: 50px;" onclick="leaveRoom()">LEAVE ROOM</button>
            </div>
        </div>
        <div class="screen-container" id="ScreenContainer">
        </div>

    </div>
    
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
    Account.data = <?=$account->getJSON()?>;
    Room.data = <?=$room->getJSON()?>;
    Messages = <?=$room->getMessages()?>;

    function updateUserInfo(accountID, nickname){

        console.log("account: ", accountID);
        console.log("nickname", nickname);
        console.log(Room.data.Accounts[accountID]);
        document.getElementById('UN' + accountID.toString()).innerHTML = nickname;
        document.getElementById('UN' + accountID.toString()+ 'mainScreen').innerHTML = nickname;
        document.getElementById("modalUsername").innerHTML = nickname;
    }

    function newUserSet(size, target) {
        if(size == 'small') {   //Small + no EventTarget sent
            for (var key in Room.data.Accounts) {
                if (Room.data.Accounts.hasOwnProperty(key)) {
                    var account = Room.data.Accounts[key];
                    //Check to make sure that this user div does not already exist
                    if(document.getElementById('NU' + key.toString()) == null) {
                        console.log("in New User Set");
                        var newUser = document.createElement('div');
                        newUser.id = 'NU' + key.toString();
                        newUser.className = 'roomSide';
                        document.getElementById('screensList').appendChild(newUser);

                        document.getElementById('NU' + key.toString()).setAttribute("onclick", "expandDiv(event)");
                        document.getElementById('NU' + key.toString()).setAttribute("ondblclick", "sendDivToCenter(event)");

                        var newUserTitle = document.createElement('div');
                        newUserTitle.id = 'UT' + key.toString();
                        newUserTitle.className = 'roomSideTitle';
                        document.getElementById('NU' + key.toString()).appendChild(newUserTitle);

                        var newUserName = document.createElement('span');
                        newUserName.id = 'UN' + key.toString();
                        newUserName.className = 'vertical-text';
                        document.getElementById('UT' + key.toString()).appendChild(newUserName);

                        document.getElementById('UN' + key.toString()).innerHTML = account.ScreenName;
                    }
                }
            }
        }
        else{   //Large + EventTarget sent
            for (var keyMS in Room.data.Accounts) {
                if (Room.data.Accounts.hasOwnProperty(keyMS)) {
                    if(target.id == 'NU' + keyMS.toString()) {
                        //This is the target Screen we want to make a large version of

                        var accountMS = Room.data.Accounts[keyMS];

                        console.log("in New User Set");
                        var newUserMS = document.createElement('div');
                        newUserMS.id = 'NU' + keyMS.toString() + 'mainScreen';
                        newUserMS.className = 'screen';
                        document.getElementById('ScreenContainer').appendChild(newUserMS);

                        var newUserTitleMS = document.createElement('div');
                        newUserTitleMS.id = 'UT' + keyMS.toString() + 'mainScreen';
                        newUserTitleMS.className = 'roomSideTitleMS';
                        document.getElementById('NU' + keyMS.toString() + 'mainScreen').appendChild(newUserTitleMS);

                        var newUserNameMS = document.createElement('span');
                        newUserNameMS.id = 'UN' + keyMS.toString() + 'mainScreen';
                        newUserNameMS.className = 'vertical-text';
                        document.getElementById('UT' + keyMS.toString() + 'mainScreen').appendChild(newUserNameMS);

                        document.getElementById('UN' + keyMS.toString() + 'mainScreen').innerHTML = accountMS.ScreenName;

                        document.getElementById('NU' + keyMS.toString() + 'mainScreen').setAttribute("onclick", "returnDivToSide(event)");
                    }
                }
            }
        }
    }
    //These all only Remain until page reload, they are wiped then.
    function expandDiv(event){
        var target = event.target;
        if(event.target.id[0] == 'N') {
            if (target != null) {
                console.log(event.target.id);
                target.className = 'eRoomSide';
                target.setAttribute("onclick", "minimizeDiv(event)");
            }
        }
    }
    function minimizeDiv(event){
        var target = event.target;
        if(event.target.id[0] == 'N') {
            if (target != null) {
                target.className = 'roomSide';
                target.setAttribute("onclick", "expandDiv(event)");
            }
        }
    }
    function sendDivToCenter(event){
        var target = event.target;
        if(target != null&& document.getElementById(target.id.toString() + 'mainScreen') == null)
        {
            newUserSet('large', target);
        }
    }
    function returnDivToSide(event){
        var target = event.target;
        if(target != null)
        {
            var item = document.getElementById(target.id);
            item.parentNode.removeChild(item);
        }
    }

</script>


<?php
/**
 * Created by PhpStorm.
 * User: isaac
 * Date: 4/22/2017
 * Time: 9:02 AM
 */

$roomid = $_GET["room"];
require_once "components/StandardHeader.php";
require_once "classes/Room.php";
require_once "classes/Account.php";

$token = $_COOKIE["Token"];
$room = new Room($roomid);
$account = Account::Login($token);

if (!$account) {
    header("HTTP/1.1 401 Unauthorized");
    header("Location: /assets/error/401.html");
}
if (!$room->accountInRoom($account)) {
    echo "Hi:";
    error_log($room->accountInRoom($account));
    exit();
}

if ($room) {
    $room_json = $room->getJSON();
} else {
//    ApacheError(404);
}


?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Room</title>

    <link rel="stylesheet" type="text/css" href="/assets/css/semantic.min.css">
    <link rel='stylesheet prefetch'
          href='https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.1.8/components/icon.min.css'>
    <link id="pagestyle" rel="stylesheet" type="text/css" href=<?php echo ($_COOKIE['theme'] == "dark") ? "/assets/css/room_dark.css" : "/assets/css/room_light.css" ?>>
    <link rel="stylesheet" href="/assets/css/range.css">
</head>


<body>

<div class="ui inverted left vertical sidebar theme1 menu">
    <div class="ui styled accordion"></div>
</div>

<div class="pusher">
    <div class="ui grid">
        <div class="row">
            <div class="ui inverted top attached theme1 menu" style="border: none !important;">
                <a class="item" id="menu">
                    <i class="sidebar icon"></i>
                    <i class="users icon"></i>
                </a>
                <p id="r-title">Room Name</p>
                <button id="share_button" class="ui circular black icon right floated theme2 button"
                        data-content="Share Your Screen" onclick="AVC.connectScreenCapture()">
                    <i class="inverted video theme1 theme2 icon"></i>
                </button>
                <button id="leave_button" class="ui circular black icon right floated theme2 button" data-content="Leave Room"
                        onclick="location='/'">
                    <i class="inverted sign out theme1 icon"></i>
                </button>
                <button id="settings_button" class="ui circular black icon right floated theme2 button"
                        onclick="openSettings('users-tab')">
                    <i id="settings_icon" class="inverted setting theme1 icon"></i>
                </button>
                <div class="ui flowing popup bottom left transition inverted theme1 hidden">
                    <div class="ui middle aligned" style="width: 15em">
                        <div class="row">
                            <div id="quick_input" style="display:none">
                                <div class="ui large fluid icon input">
                                    <input id="quick_name_change" class="quick_input" type="text" placeholder="New Screen Name..." onkeypress="if (event.keyCode == 13) changeScreenName(this.value)">
                                    <i class="checkmark link green icon" onclick="changeScreenName(previousElementSibling.value)"></i>
                                </div>
                            </div>
                            <div class="ui button fluid quickbutton" onclick="quickScreenNameChange()">Change Screen Name</div>
                        </div>
                        <div class="row">
                            <div id="quick_invite" class="fluid" style="display:none">
                                <div class="ui large fluid icon input">
                                    <input id="quick_invite_textbox" class="quick_input" value="generating..." type="text">
                                    <i id="regen-code" class="repeat link grey icon" onclick="newInvite()"></i>

                                </div>
                            </div>
                            <div id="quick-invite-button" class="ui button fluid quickbutton" onclick="quickInvite()">Create Invite Code</div>
                        </div>
                        <div class="row">
                            <div class="ui button fluid quickbutton" onclick="openSettings('audio-tab')">Media Settings</div>
                        </div>
                        <div class="row">
                            <div id="quick-theme-button" class="ui button fluid quickbutton" onclick="toggleTheme(this)"><?php echo ($_COOKIE['theme'] == "light") ? "Dark Theme" : "Light Theme" ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="content">
                <div id="right_hand_pane" class="ui right fixed inverted vertical theme1 menu"
                     style="overflow-y:scroll; padding-bottom: 3em;">
                    <div id="chat_feed" class="ui comments"></div>

                    <div>
                        <div id="file_prog" class="ui progress">
                            <div class="bar">
                                <div class="progress"></div>
                            </div>
                        </div>
                        <div id="send-box" class="ui fluid action input">
                            <input type="text" placeholder="Message..." onkeypress="if (event.keyCode == 13) sendMessage()">
                            <div id="file-upload">
                                <label for="file-input">
                                    <i class="upload icon" style="margin-top: 8px"></i>
                                </label>
                                <input id="file-input" name="upload-file" type="file" onchange="uploadFile(this.files)"/>
                            </div>
                            <div id="send-button" class="ui button" onclick="sendMessage()">Send</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="ui very wide modal">
    <div class="ui grid" style="height: 30em; ">
        <div class="four wide column">
            <div class="ui vertical fluid tabular menu">
                <a class="item users-tab" data-tab="tab-name">
                    Users
                </a>
                <a class="item invite-tab" data-tab="tab-name1">
                    Invite Codes
                </a>
                <a class="item audio-tab" data-tab="tab-name2">
                    Media Settings
                </a>
            </div>
        </div>
        <div class="twelve wide stretched column">
            <div class="ui tab" data-tab="tab-name">
                <div class="ui segment">
                    This is an stretched grid column. This AFFsegment will always match the tab height
                </div>
            </div>

            <div class="ui tab" data-tab="tab-name1" style="overflow-y: scroll">
                <table class="ui celled table" style="">
                    <thead style="position: fixed; ">
                    <tr>
                        <th style="width: 32%">Code</th>
                        <th style="width: 58%">Creator</th>
                        <th style="width: 8%">Uses</th>
                        <th colspan="3">
                            <div class="ui button green" onclick="createInvite()">Create Code</div>
                        </th>
                    </tr>
                    </thead>
                </table>
                <div class="ui inverted divider"></div>
                <table class="ui celled table" style="">
                    <tbody id="invite-code-table" style="">
                    </tbody>
                </table>
                <div class="ui inverted divider"></div>
                <table class="ui celled table" style="">
                    <thead style="position: fixed; ">
                    <tr></tr>
                    </thead>
                </table>
            </div>
            <div class="ui tab" data-tab="tab-name2">
                <div class="ui segment">
                    <h3 class="ui header">Media Settings</h3>
                    <div class="ui section divider"></div>
                    <div class="ui grid padded">

                        <div class="eight wide column">
                            <h3 class="ui header">Audio Input Device</h3>
                            <select name="audioInputDevice" class="ui dropdown fluid" id="audioSource">
                                <option value="">Default</option>
                                <option value="default">Device 1</option>
                            </select>

                            <div class="ui blue range" id="range-1"></div>
                            <div class="ui segment">
                                <label>Audio Input Volume: </label>
                                <span id="display-1"></span>
                            </div>

                        </div>
                        <div class="eight wide column">
                            <h3 class="ui header">Audio Output Device</h3>
                            <select name="audioOutputDevice" class="ui dropdown fluid" id="audioOutput">
                                <option value="">Default</option>
                                <option value="default">Device 1</option>
                            </select>

                            <div class="ui blue range" id="range-2"></div>
                            <div class="ui segment">
                                <label>Audio Output Volume: </label>
                                <span id="display-2"></span>
                            </div>
                        </div>

                        <div class="ui field">
                            <h3 class="ui header">Video Source</h3>
                            <select class="ui dropdown fluid" id="videoSource"></select>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="actions">
        <div class="ui positive right labeled icon button" onclick="closeSettings()">
            Done
            <i class="checkmark icon"></i>
        </div>
    </div>
</div>

</body>

<script
    src="https://code.jquery.com/jquery-3.1.1.min.js"
    integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
    crossorigin="anonymous">
</script>
<script src="/assets/js/jQueryRotate.js"></script>
<script src="/assets/js/semantic.min.js"></script>
<script src="/assets/js/FileSaver.js"></script>
<script src="/assets/js/sling2.js"></script>
<script src="/assets/js/room.js"></script>
<script src="/assets/js/range.js"></script>
<script src="/assets/js/peer.js"></script>
<script src="/assets/js/Autolinker.js"></script>
<script src="/assets/js/MediaStreams.js"></script>
<script src="https://webrtc.github.io/adapter/adapter-latest.js"></script>
<script src="/assets/js/common.js"></script>
<script src="/assets/js/main.js"></script>
<script>

    $(document).ready(function () {

        if(getCookie("theme") === "light")
            resetTheme();

        $('.ui.accordion')
            .accordion({
                exclusive: false
            });

        $('.tabular.menu .item').tab();

        $('.ui.left.sidebar').sidebar({
            dimPage: false,
            transition: 'overlay'
        })
            .sidebar('attach events', '#menu');

        $('.ui.dropdown').dropdown();

        $("#settings_button")
            .popup({
                inline: true,
                hoverable: true,
                position: 'bottom left',
                delay: {
                    hide: 400
                },
                onHidden: quickMenuClose
            })
            .rotate({
                bind: {
                    mouseover: function () {
                        $(this).children("i").rotate({
                            angle: 0,
                            animateTo: 45
                        })
                    }
                }
            });

        $("#regen-code")
            .rotate({
                bind: {
                    mouseover: function () {
                        $(this).rotate({
                            angle: 0,
                            animateTo: 360
                        })
                    }
                }
            });

        $("#leave_button")
            .popup();

        $("#share_button")
            .popup();

        updateScroll();

        checkInVol();
        checkOutVol();
        $('#range-1').range({
            min: 0,
            max: 100,
            start: getCookie('inVol'),
            onChange: function (value) {
                $('#display-1').html(value);
                setCookie('inVol', value, 365);
            }
        });
        $('#range-2').range({
            min: 0,
            max: 100,
            start: getCookie('outVol'),
            onChange: function (value) {
                $('#display-2').html(value);
                setCookie('outVol', value, 365);
            }
        });
        for (var key in Room.data.RoomCodes) {
            if (Room.data.RoomCodes.hasOwnProperty(key)) {
                var code = Room.data.RoomCodes[key];
                appendInviteCode(code);

            }
        }
    });

    var Account = JSON.parse('<?=$account ? $account->getJSON() : '{}'?>');
    window.addEventListener("load", function () {
//        if (window.File && window.FileList && window.FileReader) {
//            initDragDrop();
//        }
    });

    Account.data = <?=$account->getJSON()?>;
    AVC.id = Account.data.ID;
    Room.data = <?=$room->getJSON()?>;
    Messages = <?=$room->getMessages()?>;

    function openSettings(tab) {
        $('.ui.modal')
            .modal('show')
        ;
        $('.modal a.' + tab)
            .click()
        ;

    }
    function quickScreenNameChange() {
        document.getElementsByClassName("quickbutton")[0].style.display = "none";
        document.getElementById("quick_input").style.display = "inline";
        document.getElementById("quick_name_change").focus();
    }

    function quickMenuClose() {
        document.getElementsByClassName("quickbutton")[0].style.display = "block";
        document.getElementsByClassName("quickbutton")[1].style.display = "block";
        document.getElementById("quick_input").style.display = "none";
        document.getElementById("quick_invite").style.display ="none";
    }
    function closeSettings() {
        $('.ui.modal')
            .modal('hide')
        ;
    }

    function snFromId(id) {
        return Room.data.Accounts[id].ScreenName;
    }

    function appendInviteCode(code) {
        var table = document.getElementById('invite-code-table');
        var row = document.createElement('tr');
        row.innerHTML = "<td>" + code.Code + "</td><td>" + snFromId(code.Creator) + "</td><td>" + + "</td><td>" + +"</td>";
        table.appendChild(row);
    }
    function createInvite() {
        API.room.createInvite(Room.data.RoomID, function (data) {
            console.log(data);
            appendInviteCode(data.code);
        });
    }

    function toggleTheme(elem, init) {
        var themed_elems = document.getElementsByClassName("theme1");
        var colored_elems = document.getElementsByClassName("theme2");
        var themeChoice = getCookie("theme");

        if(themeChoice === "dark"){
            setCookie("theme", "light");
            resetTheme();
            swapStyleSheet("room_light.css");
        } else {
            setCookie("theme", "dark");

            [].forEach.call(themed_elems, function(e) {
                e.classList.add("inverted");
            });
            [].forEach.call(colored_elems, function(e) {
                e.classList.add("black");
            });
            swapStyleSheet("room_dark.css");
            elem.innerHTML = "Light Theme"
        }
    }

    function resetTheme() {
        var themed_elems = document.getElementsByClassName("theme1");
        var colored_elems = document.getElementsByClassName("theme2");

        [].forEach.call(themed_elems, function(e) {
            e.classList.remove("inverted");
        });
        [].forEach.call(colored_elems, function(e) {
            e.classList.remove("black");
        });
        document.getElementById("quick-theme-button").innerHTML = "Dark Theme"
    }

    function swapStyleSheet(sheet) {
        document.getElementById("pagestyle").setAttribute("href", "/assets/css/" + sheet);
    }

    function updateUserInfo(accountID, nickname) {

//        console.log("account: ", accountID);
//        console.log("nickname", nickname);
//        console.log(Room.data.Accounts[accountID]);
          Room.data.Accounts[accountID].ScreenName= nickname;
        $('.uid-'+accountID ).html(nickname);
//
//        document.getElementById('UN' + accountID.toString()).innerHTML = nickname;
//        document.getElementById('UN' + accountID.toString() + 'mainScreen').innerHTML = nickname;
//        document.getElementById("modalUsername").innerHTML = nickname;
    }

    function newUserSet(size, target) {
        if (size == 'small') {   //Small + no EventTarget sent
            for (var key in Room.data.Accounts) {
                if (Room.data.Accounts.hasOwnProperty(key)) {
                    var account = Room.data.Accounts[key];
                    //Check to make sure that this user div does not already exist
                    if (document.getElementById('NU' + key.toString()) == null) {
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
        else {   //Large + EventTarget sent
            for (var keyMS in Room.data.Accounts) {
                if (Room.data.Accounts.hasOwnProperty(keyMS)) {
                    if (target.id == 'NU' + keyMS.toString()) {
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
    function expandDiv(event) {
        var target = event.target;
        if (event.target.id[0] == 'N') {
            if (target != null) {
                console.log(event.target.id);
                target.className = 'eRoomSide';
                target.setAttribute("onclick", "minimizeDiv(event)");
            }
        }
    }

    function minimizeDiv(event) {
        var target = event.target;
        if (event.target.id[0] == 'N') {
            if (target != null) {
                target.className = 'roomSide';
                target.setAttribute("onclick", "expandDiv(event)");
            }
        }
    }
    function sendDivToCenter(event) {
        var target = event.target;
        if (target != null && document.getElementById(target.id.toString() + 'mainScreen') == null) {
            newUserSet('large', target);
        }
    }
    function returnDivToSide(event) {
        var target = event.target;
        if (target != null) {
            var item = document.getElementById(target.id);
            item.parentNode.removeChild(item);
        }
    }

</script>

</html>


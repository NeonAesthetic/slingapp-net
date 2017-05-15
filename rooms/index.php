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
    <link rel="stylesheet" href="/assets/css/room.css">
    <link id="pagestyle" rel="stylesheet" type="text/css" href=<?php echo ($_COOKIE['theme'] == "light") ? "/assets/css/room_light.css" : "/assets/css/room_dark.css"?>>
    <link rel="stylesheet" href="/assets/css/range.css">

</head>

<body>
<div id="upload-mask"></div>
<div id="upload-overlay">
    <div id="overlay-content"><p>Drag/Drop<br>Files Here</p></div>
</div>
<div class="ui inverted left vertical sidebar theme1 menu" style="top: 40px;">
    <div class="item">
        <button id="share-button" class="ui circular inverted green basic icon theme1 button"
                data-tooltip="Share Your Screen" data-position="right center" onclick="Room.checkExtension();">
            <i class="video icon"></i>
        </button>
        <button class="ui circular inverted red basic icon theme1 button"
                data-tooltip="Stop Sharing" data-position="right center" onclick="AVC.disconnectVideo()">
            <i class="remove icon"></i>
        </button>
    </div>

    <div id='video-thumbnails' class="ui inverted styled accordion" style="background: transparent; min-height: 100%">

    </div>
</div>

<div class="pusher">
    <div class="ui grid" style="height: 100vh">
        <div class="row">
            <div class="ui inverted top attached theme1 menu" style="border: none !important;">
                <a class="item" id="menu" ondragover="$('.ui.left.sidebar').sidebar('show')">
                    <i class="sidebar icon"></i>
                    <i class="users icon"></i>
                </a>
                <p id="r-title">Room Name</p>
                <button id="leave-button" class="ui circular black icon right floated theme2 button" data-content="Leave Room"
                        onclick="location='/'">
                    <i class="inverted sign out theme1 icon"></i>
                </button>
                <button id="settings-button" class="ui circular black icon right floated theme2 button"
                        onclick="Settings.openSettings('users-tab')">
                    <i id="settings-icon" class="inverted setting theme1 icon"></i>
                </button>
                <div class="ui flowing popup bottom left transition inverted theme1 hidden darkgrey">
                    <div class="ui middle aligned" style="width: 15em">
                        <div class="row">
                            <div id="quick-input" style="display:none">
                                <div class="ui large fluid icon input">
                                    <input id="quick-name-change" class="quick-input" type="text" placeholder="New Screen Name..." onkeypress="if (event.keyCode == 13) Settings.changeScreenName(this.value)">
                                    <i class="checkmark link green icon" onclick="Settings.changeScreenName(previousElementSibling.value)"></i>
                                </div>
                            </div>
                            <div class="ui button black border theme2 fluid quickbutton" onclick="Settings.quickScreenNameChange()">Change Screen Name</div>
                        </div>
                        <div class="row">
                            <div id="quick-invite" class="fluid" style="display:none">
                                <div class="ui large fluid icon input">
                                    <input id="quick-invite-textbox" class="quick-input" value="generating..." type="text" onclick="Settings.copyCode()" data-position="left center" data-variation="inverted">
                                    <i id="regen-code" class="repeat link grey icon" onclick="Settings.quickInvite(true)"></i>
                                </div>
                            </div>
                            <div id="quick-invite-button" class="ui button black border fluid theme2 quickbutton" onclick="Settings.quickInvite()">Create Invite Code</div>
                        </div>
                        <div class="row">
                            <div class="ui button black border fluid theme2 quickbutton" onclick="Settings.openSettings('audio-tab')">Media Settings</div>
                        </div>
                        <div class="row">
                            <div id="quick-theme-button" class="ui button black border fluid theme2 quickbutton" onclick="Settings.toggleTheme(this)"><?php echo ($_COOKIE['theme'] == "light") ? "Dark Theme" : "Light Theme" ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="content">
                <div id="right-hand-pane" class="ui right fixed inverted vertical theme1 menu"
                     style="overflow-y:scroll; padding-bottom: 3em;">
                    <div id="chat-feed" class="ui comments"></div>

                    <div>
                        <div id="file-prog" class="ui progress">
                            <div class="bar">
                                <div class="progress"></div>
                            </div>
                        </div>
                        <div id="send-box" class="ui fluid action input">
                            <input type="text" placeholder="Message..." onkeypress="if (event.keyCode == 13) Chat.sendMessage()">
                            <div id="file-upload">
                                <label for="file-input">
                                    <i class="upload icon" style="margin-top: 8px"></i>
                                </label>
                                <input id="file-input" name="upload-file" type="file" onchange="Chat.uploadFile(this.files)"/>
                            </div>
                            <div id="send-button" class="ui button black theme2" onclick="Chat.sendMessage()">Send</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="video-container"  class="ui inverted" style="width: 100%; height: 100%;margin: 10px; margin-right: 310px; overflow-y: scroll; position: relative; display: flex; justify-content: space-around">
            <! --- VIDEO DIV -->
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
                            <div class="ui button green" onclick="Settings.createInvite()">Create Code</div>
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
        <div class="ui positive right labeled icon button" onclick="Settings.closeSettings()">
            Done
            <i class="checkmark icon"></i>
        </div>
    </div>
</div>

<div id="plugin-prompt" class="ui basic modal">
    <i class="close icon"></i>
    <div class="ui icon header">
        <i class="archive icon"></i>
        Chrome Extension Required
    </div>
    <div class="image content">
        <div class="description">
            The SlingApp Google Chrome extension is required to stream your desktop.<br/><br/> Press OK to install the extension.
        </div>
    </div>
    <div class="actions">
        <div class="ui inverted red button"><i class="icon cancel" onclick="Room.closePluginPrompt()"></i>Cancel</div>
        <div class="ui inverted green button"><i class="icon checkmark" onclick="Room.openExtensionTab()"></i>OK</div>
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
<script src="/assets/js/Sortable.js"></script>
<script src="https://webrtc.github.io/adapter/adapter-latest.js"></script>
<script src="/assets/js/common.js"></script>
<script src="/assets/js/main.js"></script>
<script>
    var Account = JSON.parse('<?=$account ? $account->getJSON() : '{}'?>');

    Account.data = <?=$account->getJSON()?>;
    AVC.id = Account.data.ID;
    Room.data = <?=$room->getJSON()?>;
    Messages = <?=$room->getMessages()?>;
</script>

</html>


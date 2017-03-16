<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 11/20/2016
 * Time: 12:51 PM
 */

?>

<div class="abs-center card noborder flex" style="margin-top: -150px; padding: 0; width: 800px; height: 400px;box-shadow: 2px 2px 20px rgba(0,0,0,0.6)" onclick="noprop(event)">
    <div class="settings-left">
        <div class="settings-header">
            ROOM SETTINGS
        </div>
        <a id="UsersLink" class="selected" href="#Users">Users</a>
        <a id="InvitesLink" href="#Invites">Invite Codes</a>
        <a id="SoundLink" href="#Sound">Sound</a>
    </div>
    <div class="settings-right">
        <div id="Users" class="settings-panel active">
            <h1>Users</h1>
            <hr>
            <h2>Your Name</h2>
            <div id="you" style="padding-top: 15px;">
                <span class='user'></span><br>
            </div>
            <div>
                <button class="btn-circle"  style="margin: 0;" onclick="changeScreenName()">Change Nickname</button>
            </div>

            <div id="users-here" style="padding: 15px;">

            </div>
        </div>
        <div id="Invites" class="settings-panel" style="">
            <h1>Invites</h1>
            <hr>
            <table>
                <tr>
                    <td>Code</td>
                    <td>Member</td>
                    <td>Restrictions</td>
                </tr>
            </table>
            <div style="overflow:scroll; height: 50%; border-top: 1px solid lightgrey">
                <table id="invite-codes" style="">

                </table>
            </div>
            <button class="btn-circle" style="font-size: 1em; position: absolute; bottom: 0; margin-bottom: 15px;;" onclick="createInviteCode(this)">Create Invite Code</button>
            
        </div>
        <div id="Sound" class="settings-panel">
            <h1>Sound</h1>

            <div class="select">
                <label for="audioSource">Audio input source: </label><select id="audioSource"></select>
            </div>

            <div class="select">
                <label for="audioOutput">Audio output destination: </label><select id="audioOutput"></select>
            </div>

            <script>src="slingapp-net/assets/js/room.js"</script>

            <div id="player">
                <i class="fa fa-volume-down"></i>
                <div id="volume"></div>
                <i class="fa fa-volume-up"></i>
            </div>

            <script>

                $("#volume").slider({
                    min: 0,
                    max: 100,
                    value: 0,
                    range: "min",
                    slide: function(event, ui) {
                        setVolume(ui.value / 100);
                    }
                });

                var myMedia = document.createElement('audio');
                $('#player').append(myMedia);
                myMedia.id = "myMedia";

                playAudio('http://emilcarlsson.se/assets/Avicii%20-%20The%20Nights.mp3', 0);

                function playAudio(fileName, myVolume) {
                    myMedia.src = fileName;
                    myMedia.setAttribute('loop', 'loop');
                    setVolume(myVolume);
                    myMedia.play();
                }

                function setVolume(myVolume) {
                    var myMedia = document.getElementById('myMedia');
                    myMedia.volume = myVolume;
                }

            </script>



        </div>



    </div>
</div>

<script>
    window.addEventListener("load", function () {
        refreshTests();
    });
</script>



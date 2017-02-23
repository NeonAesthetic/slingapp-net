<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 11/20/2016
 * Time: 12:51 PM
 */

?>

<div class="abs-center card noborder flex" style="margin-top: -150px; padding: 0; width: 800px; height: 400px;box-shadow: 2px 2px 20px rgba(0,0,0,0.6)" onclick="ContextMenu.close(); noprop(event)">
    <div class="settings-left">
        <div class="settings-header">
            ROOM SETTINGS
        </div>
        <a id="UsersLink" class="selected" href="#Users">Users</a>
        <a id="InvitesLink" href="#Invites">Invite Codes</a>
        <a id="InvitesLink" href="#Sound">Voice</a>
    </div>
    <div class="settings-right">
        <div id="Users" class="settings-panel active">
            <h1>Users</h1>
            <hr>
            <h2>Your Name</h2>
            <div id="you" style="padding: 15px;">
                <span class="user"></span><button class="btn-circle" onclick="changeScreenName()">Change Nickname</button>
            </div>
            <div id="users-here" style="padding: 15px;">

            </div>
        </div>

        <div id="Sound" class="settings-panel active slider-width100">
            <h1>Voice</h1>
            <hr>
            <h2>Input Device</h2>
            <div id="you" style="padding: 15px;">
                <span class="user"></span><button class="btn-circle" ">Default</button>
            </div>

            <h2>Output Volume</h2>

            <div id="slider">
                <input class="bar" type="range" id="rangeinput" value="50" onchange="rangevalue.value=value"/>
                <span class="highlight"></span>
                <output id="rangevalue">50</output>
            </div>

        </div>

        <div id="Invites" class="settings-panel" style="">
            <h1>Invites</h1>
            <hr>
            <table>
                <tr>
                    <td>Code
                        </td>
                    <td>Member</td>
                    <td>Uses Left</td>
                    <td>Expiration</td>
                </tr>
            </table>
            <div style="overflow:scroll; height: 50%; border-top: 1px solid lightgrey">
                <table id="invite-codes" style="">

                </table>
            </div>
            <button class="btn-circle" style="font-size: 1em; position: absolute; bottom: 0; margin-bottom: 15px;;" onclick="createInviteCode(this)">Create Invite Code</button>
            
        </div>

    </div>
</div>

<script>
    window.addEventListener("load", function () {
        refreshTests();
    });
</script>

<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
<script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>




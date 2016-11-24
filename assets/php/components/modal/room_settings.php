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
        <a class="selected" href="#Users">Users</a>
        <a href="#Invites">Invite Codes</a>
    </div>
    <div class="settings-right">
        <div id="Users" class="settings-panel active">
            <h1>Users</h1>
            <hr>
            <h2>YOU</h2>
            <div id="you">
                <span class="user"></span><button class="btn-circle" onclick="changeScreenName()">Change Nickname</button>
            </div>
            <h2>HERE NOW</h2>
            <div id="users-here">

            </div>
        </div>
        <div id="Invites" class="settings-panel">
            <h1>Invites</h1>
            <hr>
            <button class="btn-circle" style="font-size: 1em" onclick="createInviteCode(this)">Create Invite Code</button>
            
        </div>
    </div>
</div>

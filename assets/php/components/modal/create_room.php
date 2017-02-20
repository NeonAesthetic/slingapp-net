<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 11/17/2016
 * Time: 1:06 PM
 */


?>

<div class="createrm-modal abs-center" style="margin-top:150px; position: relative">
    <center>
        <form id="createRoom"  style="padding: 20px;"
              onsubmit="return CreateRoom(event, this);" onclick="return noprop(event)">
            <div style="position: absolute; left: 0; top: 0; margin: 5px; margin-top: 0">
                <a href="#" style="color: #333; text-decoration: none" onclick="{Modal.hide()}">✕</a>
            </div>
            <h1 id="loginFormHeader" style="color: #333">Create a Room</h1>

            <input name="roomname" class="form-control nomargin" placeholder="Room Name" type="text">
            <div id="error" style="position: relative; height: 30px; color: #333; font-size: large"><br></div>
        </form>

    </center>

        <div id="submitButton" class="card-width-button" onclick="CreateRoom(event, document.getElementById('createRoom'))">SUBMIT</div>
</div>



<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 11/17/2016
 * Time: 3:41 PM
 */
?>
<div style="margin-top: -150px">
    <center>
        <form id="loginForm" class="log-modal abs-center"
              method="post"
              onsubmit="return SubmitLogin(this);" onclick="return noprop(event)">
            <div style="position: absolute; left: 0; top: 0; margin: 5px; margin-top: 0">
                <a href="#" style="color: #333; text-decoration: none" onclick="{hideLogin()}">✕</a>
            </div>
            <h1 id="loginFormHeader" style="color: #333">Login</h1>

            <input name="email" class="form-control" placeholder="email" type="email">
            <input name="pass1" class="form-control" placeholder="password" type="password">
            <div id="error" style="position: relative; height: 30px; color: #333; font-size: large"><br></div>
            <hr>
            <div id="submitButton" class="sbtn card-width-button" onclick="submitLogin(this)">SUBMIT</div>
        </form>
    </center>

</div>

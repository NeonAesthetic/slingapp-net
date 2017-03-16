<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 11/17/2016
 * Time: 3:41 PM
 */
?>
<div class="log-modal abs-center">
    <center>
        <div style="position: absolute; left: 0; top: 0; margin: 5px; margin-top: 0">
            <a href="#" style="color: #333; text-decoration: none" onclick="{hideLogin()}">✕</a>
        </div>
        <form id="registerForm" class="full-height"
              method="post"
              onsubmit="return submitRegister();" onclick="return noprop(event)">

            <h2 id="loginFormHeader" style="color: #333; padding-bottom: 10%">Create an Account</h2>
            <input name="fname" class="form-control" placeholder="first name" type="text" onkeydown="clearError()">
            <input name="lname" class="form-control" placeholder="last name" type="text" onkeydown="clearError()">
            <input name="email" class="form-control" placeholder="email" type="email" onkeydown="clearError()">
            <input name="pass1" class="form-control" placeholder="password" type="password" onkeyup="checkPasswords(this.parentNode)">
            <input name="pass2" class="form-control" placeholder="confirm password" type="password" onkeyup="checkPasswords(this.parentNode)">
            <div id="registererror" class="error-message"><br></div>
        </form>

    <div id="divider">
    </div>

        <form id="loginForm" class="full-height"
              method="post"
              onsubmit="return SubmitLogin(this);" onclick="return noprop(event)">

            <h2 id="loginFormHeader" style="color: #333; padding-bottom: 10%">Login</h2>
            <input name="email" class="form-control" placeholder="email" type="email" onkeydown="clearError()">
            <input name="pass1" class="form-control" placeholder="password" type="password" onkeydown="clearError()">
            <div id="loginerror" class="error-message"><br></div>
        </form>
    <div style="width: 100%; float: left">

        <div id="submitReg" class="card-width-button" style="width: 50%; float:left" onclick="noprop(event);submitRegister(this)">SIGN UP</div>
        <div id="submitLogin" class="card-width-button" style="width: 50%; float:left" onclick="noprop(event);submitLogin(this)">LOGIN</div>
    </div>
    </center>
</div>

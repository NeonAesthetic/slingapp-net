<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/14/16
 * Time: 8:09 AM
 */
session_start();
set_include_path(realpath($_SERVER['DOCUMENT_ROOT']) . "/assets/php/");
require_once "components/Components.php";
?>
<html>
<head>
    <title>
        Sharing Site
    </title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body style="background-color: #38474F; overflow: hidden; padding-top: 50px;">
<div id="modal" onclick="hideModal()">

</div>

<div id="login-cont"
     style="position: fixed; width: 100%; height: 100%; visibility: hidden; color: white; z-index: 99999999999;"
     onclick="hideLogin()">

    <center>
        <form id="loginForm" class="log-modal"
              style="" method="post"
              onsubmit="return SubmitLogin(this);" onclick="return noprop(event)">
            <div style="position: absolute; left: 0; top: 0; margin: 5px; margin-top: 0">
                <a href="#" style="color: #333; text-decoration: none" onclick="hideLogin()">✕</a>
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
<nav class="navbar navbar-fixed-top">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand noselect" href="/"><span class="glyphicon glyphicon-blackboard"
                                                        style="font-size: 24px"> </span>SLING</a>
    </div>
    <div id="navbar" class="navbar-collapse collapse">
        <ul class="nav navbar-nav navbar-left">
            <li>
                <div class="btn-group navbar-btn">

                    <ul id="context-menu" class="dropdown-menu context-menu"
                        style="padding: 5px 0 5px 0; margin-top: 15px;">
                        <li>
                            Login
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
            <li>
                <div class="btn-group navbar-btn">
                    <button id="login-button" class="login-button" onclick="(loggedIn ? logout() : showLogin())">Login</button>
                </div>
            </li>
        </ul>
    </div>
</nav>
<div class="container-fluid"
     style="background-color: rgba(255,255,255,1); text-align: center; padding: 50px;; ">
    <a class="register-link" href="/register/" style="position: absolute; top: 0; right: 0; margin: 60px;">Register</a>

    <h1 style="width:100%; text-align: center;">Sharing so easy you'll never go back</h1>
    <div class="wrapper">
        <div class="screenshot">
        </div>
    </div>

</div>

<div class="container-fluid" style="text-align: center; position: fixed; bottom: 0; width: 100%; padding: 50px;">
    <div class="" style="margin: 0 auto;">
        <button class="btn-main" style=" width: 200px; margin: 30px; display: inline-block"
                onclick="modal('Create Room Modal')">
            Create Room
        </button>

        <form class="" style="margin: 30px; display: inline-block" onsubmit="joinroom(event, this);">
            <input name="room" class="mp-form" type="text" size="8" style="width: 200px; padding-left: 20px;"
                   value="Join a Room" placeholder="Room Code" onfocus="toggleform(this)" onblur="toggleform(this)">
        </form>
    </div>

</div>

</body>
<script type='text/javascript' src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.1.1.min.js"></script>
<script type='text/javascript' src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script type='text/javascript' src="/assets/js/sling.js"></script>
<!-- use enter button to submit login info-->
<script>
    document.getElementById('loginForm').onkeypress = function (e) {
        if (e.keyCode == 13) {
            document.getElementById('submitButton').click();
        }
    }
    /** PAGE SETUP HERE **/
    window.addEventListener("load", function () {
        Resource.load("/assets/php/components/modal/create_room.php", "Create Room Modal");
    });
</script>

<script>
    function toggleform(e) {
        if (e.value === "Join a Room") {
            e.value = "";
            e.style.color = "black";
            e.style.backgroundColor = "#fefefe";
        }
        else if (e.value === "") {
            e.value = "Join a Room";
            e.style.color = "white";
            e.style.backgroundColor = "transparent";
        } else {
        }
    }
    function joinroom(event, f) {
        event.preventDefault();
        console.log(f);
        var room = f["room"].value;
        window.location = "/room/" + room;
        return false;
    }
    function showLogin() {
        var button = document.getElementById("login-button");
        button.className += " open";
        setTimeout(function () {
            var loginarea = document.getElementById("login-cont");
            loginarea.style.visibility = "visible";
            console.log(loginarea);
        }, 700);
    }

    function logout() {
        console.log("in logout");
        var form = document.getElementById("loginForm");
        var email = form.elements["email"].value;
        var password = form.elements["pass1"].value;
        return $.ajax({
            type: 'post',
            url: 'assets/php/components/account.php',
            data: {
                action: "logout"
            },
            success: function () {
                isTokenSet();
            },
            error: function (error) {
                console.log(error);
            }
        });
    }

    function submitLogin() {
        var form = document.getElementById("loginForm");
        var email = form.elements["email"].value;
        var password = form.elements["pass1"].value;
        var errorDiv = document.getElementById("error");
        errorDiv.innerHTML = "<div class='sling' style=''></div>";
        return $.ajax({
            type: 'post',
            url: 'assets/php/components/account.php',
            dataType: 'JSON',
            data: {
                action: "login",
                email: email,
                pass1: password
            },
            success: function (data) {
                noErrors(data);
                return data;
            },
            error: function (error) {
                console.log(error);
            }
        });
    }
    function noErrors(data) {
        var loginError = document.getElementById("error");
        //console.log(data);
        if (data) {
            loginError.innerHTML = "<br>";
            hideLogin(data)
        }
        else
            loginError.innerHTML = "Username or password is Incorrect";
    }

    function hideLogin(data) {
        var button = document.getElementById("login-button");
        var loginarea = document.getElementById("login-cont");
        button.innerHTML = "Logout";
        button.className = "login-button";
        loginarea.style.visibility = "hidden";
        isTokenSet();
    }
    function noprop(e) {
        e.stopPropagation();
        return false;
    }
    window.addEventListener("load", function () {
        var sbtns = document.getElementsByClassName("sbtn");
        for (var i = sbtns.length - 1; i >= 0; i--) {
            console.log(sbtns[i]);
            var button = sbtns[i];
            button.addEventListener("click", function (event) {
                var drip = document.createElement("div");
                var brect = button.getBoundingClientRect();
                drip.className = "drip";
                drip.style.left = event.pageX - brect.left - 50;
                drip.style.top = event.pageY - brect.top - 50;
                setTimeout(function () {
                    button.removeChild(drip)
                }, 1000);
                button.appendChild(drip);
            })
        }
    });

    function modal(resourceName) {
        var modalContents = Resource.get(resourceName);
        if (modalContents != null) {
            document.getElementById("modal").style.visibility = "visible";
            document.getElementById("modal").innerHTML = modalContents;
        } else {
            console.error("Resource " + resourceName + " has not been loaded.  Load the resource first with Resource.load()");
        }
    }
    function hideModal() {
        document.getElementById("modal").style.visibility = "hidden";
    }

    function isTokenSet() {
        var allcookies = document.cookie;
        var button = document.getElementById("login-button");
        var loggedin = false;
        var name;
        var value;

        cookiearray = allcookies.split(';');

        for (var i = 0; i < cookiearray.length; i++) {
            name = cookiearray[i].split('=')[0];
            value = cookiearray[i].split('=')[1];
            if (name == 'token' && value != '') {//check if token has value and is valid
                button.innerHTML = "Logout";
                loggedin = true;
            }
        }
        if(!loggedin)
            button.innerHTML = "Login";

        return loggedin;
    }
</script>
</html>
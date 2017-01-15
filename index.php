<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/14/16
 * Time: 8:09 AM
 */
set_include_path(realpath($_SERVER['DOCUMENT_ROOT']) . "/assets/php/");
require_once "components/Components.php";
require_once "classes/Account.php";

?>
<html>
<head>
    <title>
        Sharing Site
    </title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body style="overflow: hidden; ">

<nav class="navbar " style="z-index: 999999">
    <div class="container-fluid">
        <div class="navbar-header">
            <!--        Needs hamburger icon-->
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <a class="navbar-brand" href="/"><span class="glyphicon glyphicon-blackboard"
                                                   style="font-size: 24px"> </span>SLING</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <button id="login-button" class="login-button" onclick="(GetToken()) ? logout() : showLogin()"
                            style="margin: 5px;">Login<span id="reg"><br>or sign up</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div style="background-color: rgba(255,255,255,1); text-align: center; ">

    <h1 style="width:100%; text-align: center; padding-top: -10%;">Sharing so easy you'll never go back</h1>
    <div class="wrapper">
        <div id="screenshot" class="screenshot">
        </div>
    </div>

</div>

<div class="container-fluid"
     style="text-align: center; position: fixed; bottom: 0; width: 100%; background: #38474F;">
    <div style="margin: 0 auto;">
        <button class="btn-main"
                style=" width: 200px; margin: 15px; margin-left: 30px; margin-right: 30px; display: inline-block"
                onclick="Modal.create('Create Room Modal', 'darken', null)">
            Create Room
        </button>

        <form class="" style="margin: 15px; margin-left: 30px; margin-right: 30px; display: inline-block"
              onsubmit="joinroom(event, this);">
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
    /** PAGE SETUP HERE **/

    isLoggedIn(GetToken());

    window.addEventListener("load", function () {
        Modal.init();
        Resource.load("/assets/php/components/modal/create_room.php", "Create Room Modal");
        Resource.load("/assets/php/components/modal/login_form.php", "Login Form");

<<<<<<< HEAD
    loggedIn = isTokenSet();

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
        var code = f["room"].value;
        $.ajax({
            type: 'post',
            url: 'assets/php/components/room.php',
            dataType: 'JSON',
            data: {
                action: "join",
                token:GetToken(),
                code:code
            },
            success: function (data) {
                var roomid = data.RoomID;
                console.log(data);
                window.location = "/rooms/" + roomid;
            },
            error: function (error) {
                console.log(error);
            }
        });

        return false;
    }
    function showLogin() {
        var button = document.getElementById("login-button");
        button.className += " open";
        setTimeout(function(){
            Modal.create("Login Form", "", function () {
                hideLogin();
            })}, 700
        );
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
        Modal.hide();
        isTokenSet();
    }

    function noprop(e) {
        e.stopPropagation();
        return false;
    }
    window.addEventListener("load", function () {
=======
>>>>>>> a9f9072d6ca63112d57778b8e2ee2d51feb039df
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
</script>
</html>
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
<!DOCTYPE html>
<html>
<head>
    <title>
        Sharing Site
    </title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/assets/css/sling.css">
</head>
<!--<body style="overflow: hidden; ">-->
<body style="background-color: #38474F; overflow: hidden">
<div class="slingBackground" id="grad1">
    <div class="slingContentContainer">
        <div class="slingContentDiv" style=" background-color: #333333; border 6px solid black; height: 76%;">
            <div class="slingContentDiv" style="background-color: #333333; width: 98%; height: 10%; margin-left: 1%; margin-right: 1%; margin-top: 4%;">
                <span style="margin-left: 12%; color:white;font-weight: lighter; font-family:'Century Gothic'; min-font-size: small; font-size: 200%;">Your Recent Rooms</span>
            </div>
            <div class="slingContentDiv" id= "RecentRooms" style="background-color: transparent; width: 98%; height: 80%; margin-left: 1%; margin-right: 1%;">

            </div>
        </div>
        <div class="slingContentDiv" style="border: none; height: 76%; margin-left: 1%; margin-right: 1%; margin-top: 6%; width: 32%; min-width: 240px;">
            <div class="slingContentDiv" style="background-color: #333333; width: 98%; height: 20%; margin-left: 1%; margin-right: 1%; margin-top: 10%;">
                <img style="margin-left: 22%; margin-top: -7%;" src="favicon.ico" alt="Sling S" height="100" width="70">
                <span style="margin-left: 1%; color:black; font-weight: lighter; font-family:'Century Gothic'; min-font-size: large; font-size: 550%;">L I N G</span>
            </div>
            <div class="slingContentDiv" style="background-color: #333333; width: 98%; height: 40%; margin-left: 1%; margin-right: 1%; margin-top: 4%;">
                <div class="slingContentDiv" style="background-color: transparent; width: 98%; height: 85%; margin-left: 1%; margin-right: 1%;">
                    <span style="color:white;">This Is The Description Of The Sling Application</span>
                </div>
            </div>
            <div class="slingContentDiv" style=" background-color: #38474F; width: 98%; height: 16%; margin-left: 1%; margin-right: 1%; margin-top: 1%;">
                <button class="sling-btn-main"
                  style=" width: 200px; margin: 2%; margin-left: 8%;  display: inline-block"
                  onclick="Modal.create('Create Room Modal', 'darken', null)">
                  Create Room
                </button>
                <form class="" style="width: 200px; margin: 2%; margin-left: 8%; margin-top: 4%; display: inline-block"
                      onsubmit="joinroom(event, this);">
                    <input name="room" class="sling-mp-form" type="text" size="8" style="width: 100%; padding-left: 30px;"
                           value="Join Room" placeholder="Room Code" onfocus="toggleform(this)" onblur="toggleform(this)">
                </form>
            </div>

        </div>
        <div class="slingContentDiv" style=" background-color: #333333; border 6px solid black; height: 76%;">
            <div class="slingContentDiv" style="background-color: #333333; width: 98%; height: 10%; margin-left: 1%; margin-right: 1%; margin-top: 4%;">
                <span style="margin-left: 20%; color:white;font-weight: lighter; font-family:'Century Gothic'; min-font-size: small; font-size: 200%;">Recent Updates</span>
            </div>
            <div class="slingContentDiv" style="background-color: transparent; width: 98%; height: 80%; margin-left: 1%; margin-right: 1%;">
                <!-- start feedwind code --> <script type="text/javascript" src="https://feed.mikle.com/js/fw-loader.js" data-fw-param="12535/"></script> <!-- end feedwind code -->
            </div>
        </div>
    </div>
</div>

<nav id="LoggedInNavBar" style="visibility: hidden">
<ul>
    <li class="drop">
        <div class="user-avatar"><img src="http://www.stedesign.com/ste.png"></div><a id="NavName" href="#">Stefano</a> <span aria-hidden="true" class=
        "glyphicon glyphicon-menu-hamburger orange-txt"></span>
        <div class="triangle"></div>
        <div class="dropdownContain">
            <div class="dropOut">
                <ul>
                    <li onclick="logout()"><span aria-hidden="true" class="icon-off"></span> Log Out</li>
                    <li onclick=""><span aria-hidden="true" class="icon-off"></span> Settings</li>
                </ul>
            </div>
        </div>
    </li>
</ul>
</nav>

<nav id="LoggedOutNavBar" class="navbar " style="z-index: 999999">
    <div class="container-fluid">
        <div class="navbar-header">
            <!--        Needs hamburger icon-->
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <button id="login-button" class="login-button" onclick="showLogin()"
                            style="margin: 5px;">Login<span id="reg"><br>or sign up</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid"
               style="text-align: center; position: fixed; bottom: 0; width: 100%; background: #38474F;">
        <div style="margin: 0 auto; height: 20%;">
<!--            <button class="btn-main"-->
<!--                    style=" width: 200px; margin: 15px; margin-left: 30px; margin-right: 30px; display: inline-block"-->
<!--                    onclick="Modal.create('Create Room Modal', 'darken', null)">-->
<!--                Create Room-->
<!--            </button>-->
<!---->
<!--            <form class="" style="margin: 15px; margin-left: 30px; margin-right: 30px; display: inline-block"-->
<!--                  onsubmit="joinroom(event, this);">-->
<!--                <input name="room" class="mp-form" type="text" size="8" style="width: 200px; padding-left: 20px;"-->
<!--                       value="Join Room" placeholder="Room Code" onfocus="toggleform(this)" onblur="toggleform(this)">-->
<!--            </form>-->
        </div>
</div>



</body>
<script type='text/javascript' src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.1.1.min.js"></script>
<script type='text/javascript' src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script type='text/javascript' src="/assets/js/sling.js"></script>
<iframe src="https://feed.mikle.com/widget/v2/12535/"></iframe>
<script>

    isLoggedIn();
    getRoomData();
    window.addEventListener("load", function () {
        Modal.init();
        Resource.load("/assets/php/components/modal/create_room.php", "Create Room Modal");
        Resource.load("/assets/php/components/modal/login_form.php", "Login Form");

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
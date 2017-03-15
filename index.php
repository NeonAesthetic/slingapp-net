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
require_once "classes/Room.php";

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
<body>
<div class="slingBackground">
    <div class="sling-feed-container" style="margin: auto">
        <div class="is-flex">
            <button class="slingButton slingButtonCR"
                    onclick="Modal.create('Create Room Modal', 'darken', null)">
                Create Room
            </button>
            <form class="slingForm slingFormJR"
                  onsubmit="joinroom(event, this);">
                <input name="room" class="slingFormInner" type="text" size="14"
                       value="Join Room" placeholder="Room Code" onfocus="toggleform(this)" onblur="toggleform(this)">
            </form>
            <!-- start feedwind code --> <script type="text/javascript" src="https://feed.mikle.com/js/fw-loader.js" data-fw-param="12535/"></script> <!-- end feedwind code -->
        </div>
    </div>
</div>
<div class="slingBackground slingBackgroundUpper"></div>
<div class="container container-main-page">
    <div class="sling-title-container">
        <img class="sling-title" src="SlingLogo.png" title="Sling"/>
        <div class="is-flex">
            <div class="motto-div" style="border-right: 2px solid lightgray">
                SHARE
            </div>
            <div class="motto-div">
                STREAM
            </div>
            <div class="motto-div" style="border-left: 2px solid lightgray">
                CONNECT
            </div>
        </div>
    </div>
</div>

<!--<div class="slingBackground slingBackgroundUpper">
    <div class="slingContentDiv slingContentDivTitle">
        <img style="height: 100%; width: 100%; object-fit: contain" src="SlingLogo.png" alt="Sling S">
    </div>
    <div class="slingContentDiv slingContentDivMotto">
        <div class="slingContentDivMottoInner">
            <div class="slingContentDivCenterText">SHARE</div>
        </div>
        <div class="slingContentDivMottoInner" style="border-right: solid 3px cadetblue;border-left: solid 3px cadetblue; margin-left: 20vw">
            <div class="slingContentDivCenterText">STREAM</div>
        </div>
        <div class="slingContentDivMottoInner" style=" margin-left: 40vw">
            <div class="slingContentDivCenterText">CONNECT</div>
        </div>
    </div>
</div>-->


<!--<div class="slingContentDiv slingContentDivButtons">-->
<!--    <button class="slingButton slingButtonCR"-->
<!--            onclick="Modal.create('Create Room Modal', 'darken', null)">-->
<!--        Create Room-->
<!--    </button>-->
<!--    <form class="slingForm slingFormJR"-->
<!--          onsubmit="joinroom(event, this);">-->
<!--        <input name="room" class="slingFormInner" type="text" size="8"-->
<!--               value="Join Room" placeholder="Room Code" onfocus="toggleform(this)" onblur="toggleform(this)">-->
<!--    </form>-->
<!--</div>-->


<!--<nav class="navbar " style="z-index: 999999">
    <div class="container-fluid">
        <div class="navbar-header">

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
                    <button id="login-button" class="login-button" onclick="(isLoggedIn()) ? logout() : showLogin()"
                            style="margin: 5px;">Login<span id="reg"><br>or sign up</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>-->

<!--<div class="container-fluid"
               style="text-align: center; position: fixed; bottom: 0; width: 100%; background: #38474F;">
        <div style="margin: 0 auto; height: 20%;">
            <button class="btn-main"
                    style=" width: 200px; margin: 15px; margin-left: 30px; margin-right: 30px; display: inline-block"
                    onclick="Modal.create('Create Room Modal', 'darken', null)">
                Create Room
            </button>

            <form class="" style="margin: 15px; margin-left: 30px; margin-right: 30px; display: inline-block"
                  onsubmit="joinroom(event, this);">
                <input name="room" class="mp-form" type="text" size="8" style="width: 200px; padding-left: 20px;"
                       value="Join Room" placeholder="Room Code" onfocus="toggleform(this)" onblur="toggleform(this)">
            </form>
        </div>
</div>-->

</body>
<script type='text/javascript' src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.1.1.min.js"></script>
<script type='text/javascript' src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script type='text/javascript' src="/assets/js/sling.js"></script>
<!--<iframe src="https://feed.mikle.com/widget/v2/12535/"></iframe>-->
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
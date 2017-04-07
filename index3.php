<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/14/16
 * Time: 8:09 AM
 */

?>
<!DOCTYPE html>
<html>
<head>
    <title>
        Sling
    </title>

<!--    <link rel="stylesheet" href="/assets/css/semantic.min.css">-->
    <link rel="stylesheet" href="/assets/css/sling.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<!--<body style="overflow: hidden; ">-->
<body>
<?php

//    include "components/navbar.php";

?>
<div class="container">
    <div class="sling-feed-container" style="margin: auto">
        <div class="is-flex" >
            <button class="slingButton" onclick="Modal.create('Create Room Modal', 'darken', null)">
                Create Room
            </button>
            <form class="slingButton" onsubmit="joinroom(event, this);">
                <input name="room" class="slingFormInner" type="text" size="14" value="Join Room" placeholder="Room Code" onfocus="toggleform(this)" onblur="toggleform(this)">
            </form>
            <div style="width: 100%; margin-top: 50px; height: 150px">
                <!-- start feedwind code --> <script type="text/javascript" src="https://feed.mikle.com/js/fw-loader.js" data-fw-param="12535/"></script> <!-- end feedwind code -->
            </div>

        </div>
    </div>
</div>
<div class="slingBackground slingBackgroundUpper"></div>
<div class="container container-main-page">
    <div class="sling-title-container">
        <div class="contains-image" style="background-image: url('slingblock.png'); height: 260px; width: 100%;"></div>
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

<script type='text/javascript' src="/assets/js/sling.js"></script>
<!--<iframe src="https://feed.mikle.com/widget/v2/12535/"></iframe>-->
<script>


    window.addEventListener("load", function () {
        isLoggedIn();
        getRoomData();

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
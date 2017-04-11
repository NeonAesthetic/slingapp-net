<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/14/16
 * Time: 8:09 AM
 */

require_once "classes/Account.php";
$token = $_COOKIE['Token'];
$account = Account::Login($token);
if(!$account){
    $account = Account::CreateAccount();
    setcookie("Token", $account->getToken(), time() + 60 * 60 * 24 * 7, "/");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>
        Sling
    </title>

    <link rel="stylesheet" href="/assets/css/semantic.min.css">
    <link rel="stylesheet" href="/assets/css/extra.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">
    <style>
        .contains-image{

            background-size: contain;
            background-position: center center;
            background-repeat: no-repeat;
            position: relative;
        }


    </style>
</head>
<body>
    <div class="ui grid">
        <!--   NAVBAR     -->
        <?php
            include "components/navbar.php";
        ?>
        <!--  END NAVBAR  -->

        <!-- BEGIN MAIN CONTENT  -->

        <!-- BIG THING WITH IMAGE -->
        <div class="contains-image column sixteen wide" style="background-image: url('/assets/images/lake.jpg'); width: 100%; background-size: cover; padding: 0;">
            <div class="" style="background-color: rgba(0,0,0,0.85);padding: 10em 0 10em 0">
                <div class="ui container text" style="color: white; text-align: center;">
                    <h1 style="font-size: 2.7em">Sling lets you share and collaborate better than ever before.</h1>
                    <p style="font-size: 1.5em; color: #ccc; font-weight: 100">
                        Text chat, voice chat, file sharing, and screen sharing all encapsulated in a private, secure Room.
                    </p>
                    <p>
                        <button class="ui button inverted blue huge" onclick="$('.ui.register').modal('show')">Sign Up For Free</button>

                    </p>

                    <p style="color: #bbb">
                        Already have an account? <a href="#" class="ui link" onclick="$('#login-dropdown').dropdown('show')">Sign In</a>
                    </p>
                </div>
            </div>
        </div>
        <!-- SCREENSHOTS -->
        <div class="column sixteen wide" style="background-image: url('/assets/images/dgrey-noise.png'); box-shadow: 0 4px 10px rgba(0,0,0,0.5) inset; padding: 7em 0 7em 0; position: relative">
            <div class="ui container text" style="text-align: center;font-size: 2em; color: #ccc; font-weight: 100">
                <p>Make or Join a Room, no account necessary.</p>
                <p>
                    <button class="ui button inverted blue huge" onclick="Room.showCreateRoomDialog()">Make a Room</button>
                    <button class="ui button inverted blue huge" onclick="Room.showJoinRoomDialog()">Join a Room</button>
                </p>

            </div>

                <img class="ui image rounded" style="margin: 5em auto 5em auto; max-width: 1200px; width: 100%" src="/assets/images/room.png" >

            <div class="ui container text" style="text-align: center; font-size: 2em; color: #ccc;">
                <p>
                    Create a Room to share to your heart's content, then securely delete it afterwards.
                </p>
                <p>
                    All data is transfered over SSL and video and audio streams never even touch our servers.
                </p>

            </div>
            <div class="rss-feed">

            </div>
        </div>
        <!-- FOOTER -->
        <footer class="ui column sixteen wide" style="background-color: #111; box-shadow: 0 -4px 10px rgba(0,0,0,0.5); color: #888;padding: 50px; text-align: center">
            <div class="ui container text" style="display: flex; justify-content: space-around; align-items: center;font-size: 1.5em">
                <a class="ui link" href="https://github.com/NeonAesthetic/slingapp-net/issues">Submit a bug</a>
                <a href="https://github.com/NeonAesthetic/slingapp-net/wiki">Read the documentation</a>
                <a href="mailto:ian@slingapp.net">Contact</a>
            </div>
            <br>
            <p>
                <span>&copy; Copyright 2017, Sling.  All rights reserved.</span>
            </p>

        </footer>
    </div>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="/assets/js/semantic.min.js"></script>
<script type='text/javascript' src="/assets/js/sling2.js"></script>

<script>
    Account.data = JSON.parse('<?=$account ? $account->getJSON() : '{}'?>');
    window.addEventListener("load", function () {

//        isLoggedIn();
//        getRoomData();


        $('.ui.dropdown')
            .dropdown({
                on:'hover'
            })
        ;

        $('.ui.dropdown.login')
            .dropdown({
                on:'click',
                action:'nothing'
            })
        ;

        $('.ui.modal').modal({
            onApprove:function () {
                return false;
            },
            approve:'.positive'
        });

        $('input').popup({
            on:'manual'
        });

        $('input').on("blur", function () {
            $(this).popup('hide');
        });

        Feed.init();

    });



</script>

</html>
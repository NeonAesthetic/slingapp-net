<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/14/16
 * Time: 8:09 AM
 */

?>
<html>
<head>
    <title>
        Sharing Site
    </title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body style="background-color: #4286f4;">
    <div class="container-fluid" style="background-color: rgba(255,255,255,1); text-align: center; padding-bottom: 30px; min-height: 50vh">
        <h1 style="width:100%; text-align: center;">Sharing so easy you'll never go back</h1>
        <div class="wrapper">
            <div class="screenshot">
            </div>
        </div>

    </div>

    <div class="container" style="text-align: center; ">
        <div class="col-lg-6" >
            <button class="btn-main" style=" width: 200px; margin: 30px">
                Share Screen
            </button>
        </div>
        <div class="col-lg-6">
            <form class="" style="margin: 30px;" onsubmit="joinroom(event, this);">
                <input name="room" class="mp-form" type="text" size="8" style="width: 200px; padding-left: 20px;" value="Join a Room" placeholder="Room Code" onfocus="toggleform(this)" onblur="toggleform(this)">
            </form>
        </div>
    </div>

</body>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script>
    function toggleform(e) {
        if(e.value === "Join a Room"){
            e.value = "";
            e.style.color = "black";
            e.style.backgroundColor = "#fefefe";
        }
        else if(e.value === ""){
            e.value = "Join a Room";
            e.style.color = "white";
            e.style.backgroundColor = "transparent";
        }else{

        }
    }

    function joinroom(event, f) {
        event.preventDefault();
        console.log(f);
        var room = f["room"].value;
        window.location = "/room/"+room;
        return false;
    }
</script>
</html>

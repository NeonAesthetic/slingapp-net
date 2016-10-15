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
    <link rel="stylesheet" href="custom.css">
</head>
<body>
<div class="screenshot" style="width: 600px; min-height: 400px; top: 150px; text-align: center">

</div>
<h1 style="width:100%; text-align: center; position: absolute; top: 80px;">Sharing so easy you'll literally die</h1>
<div class="container-fluid" style="text-align: center; background-color: #4286f4; position: relative; top: 80%; height: 20%">
    <div class="container" style="margin-top: 50px;">

        <div class="col-lg-6">
            <button class="btn-main" style=" width: 200px">
                Share Screen
            </button>
        </div>
        <div class="col-lg-6">
            <form class="" style="" >
                <input name="room" class="mp-form" type="text" size="8" style="width: 200px; padding-left: 20px;" value="Join a Room" placeholder="Room Code" onclick="onetimechange(this)">
            </form>
        </div>
    </div>
</div>
</body>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script>
    function onetimechange(e) {
        e.value = "";
        e.style.color = "black";
        e.style.backgroundColor = "#fefefe";
        e.onclick = null;
    }
</script>
</html>

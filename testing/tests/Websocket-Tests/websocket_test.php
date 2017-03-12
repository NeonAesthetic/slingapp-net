<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 11/18/2016
 * Time: 3:38 PM
 * Type: JS
 * Test Name: Basic Connect
 */

?>

<script>
function test(test){
    test.log("Socket");
    var url = "ws://localhost:8001/rooms/socket.php";
    var Socket = new WebSocket(url);
    Socket.onopen = function(){
        test.log("Connected to server");
        Socket.send("Hello");
        test.end(true)
        return
    };
    Socket.onmessage = function(evt){
        var message = evt.data;
        test.log(message);
    };
    setTimeout(function () {
        test.end(false);
    }, 2000);
}



</script>

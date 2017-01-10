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
function test(console){
    console.log("Socket");
    var url = "ws://localhost:8001/rooms/socket.php";
    var Socket = new WebSocket(url);
    Socket.onopen = function(){
        console.log("Connected to server");
        Socket.send("Hello");
    };
    Socket.onmessage = function(evt){
        var message = evt.data;
        console.log(message);
    };
}


//    assert(1==2, "assert that 1 == 2");


</script>

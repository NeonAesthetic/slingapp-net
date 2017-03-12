<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 11/18/2016
 * Time: 3:38 PM
 * Type: JS
 * Test Name: Room Functionality Test
 */

?>

<script>

    function connectToRoomSocket(id, token, test){
        var url = "wss://localhost/rooms/"+id;
        var Socket = new WebSocket(url);
        Socket.onopen = function(){
            test.log("Connected to server");
            Socket.send(JSON.stringify({
                action:"Register",
                token:token
            }));
        };
        Socket.onmessage = function(evt){
            var message = JSON.parse(evt.data);
            if(message.success == true){
                test.log("Successfully registered with Room");
                test.end(true);
            }
        };

        Socket.onerror = function (err) {
            test.log(err);
            console.log(err);
            test.end(false);
        }
//        setTimeout(function () {
//            test.end(false);
//        }, 2000);
        return Socket;
    }


    function test(test){
        var Socket = null;
        var token = GetToken();
        if(!token){
            test.end(false);
            return;
        }
        test.log("Using token: [" + token +"]");

        get("/assets/php/components/room.php", "?action=create&token=" + token, function(text){
            var results = false;
            try{
                results = JSON.parse(text);
            }catch (e){
                test.end(false);
            }
            if(results.Type == "Room"){
                test.log("Connecting to Room: [" + results.RoomID + "]");
                Socket = connectToRoomSocket(results.RoomID, token, test)

            }else{
                test.end(false);
            }
        })



    }



</script>

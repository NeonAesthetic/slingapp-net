<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 11/19/16
 * Time: 11:39 AM
 * Test Name: NOINCLUDE
 */

?>

<form onsubmit="return websock_submit(event, this)">
    <select name="action">
        <option value="">Action</option>
        <option value="register">register</option>
        <option value="Change Name">Change Name</option>
        <option value="send_message">Send Message</option>
        <option value="send_file">Send File</option>
        <option value="other">Other</option>
    </select>
    <input name="user" type="text" placeholder="username">
    <button id="TestButton">Submit</button>
</form>
<script>
    //On Submit button, invoke alter_name func

</script>
<script type='text/javascript' src="/assets/js/sling.js"></script>
<script>
    Websocket = null;
    token = GetToken();
    var roomSnowflake = "14948841492905591106";
    window.addEventListener("load", function () {
        var url = "ws://localhost:8001/rooms/".concat(roomSnowflake.toString());
        Websocket = new WebSocket(url);
        Websocket.onopen = function(){
            console.log("Open");
            var json = {
                "token":token,
                "action":"Register"
            };
            Websocket.send(JSON.stringify(json));
        };
        Websocket.onmessage = function(evt){
            console.log(evt.message);
        };
    });

//    document.getElementById("TestButton").addEventListener("click", function() {
//        var json = {
//            "room":roomSnowflake,
//            "token":token,
//            "action":"Change Name"
//        };
//        Websocket.send(JSON.stringify(json));
//        Websocket.onmessage = function(evt){
//                console.log(evt.message);
//        };
//    });
    function websock_submit(event, form){
        event.preventDefault();
        event.stopPropagation();
        var token = GetToken();
        form = document.getElementsByTagName('form')[0];
//        console.log(form.action.value);

        var json = {
            action:form.action.value,
            user:form.user.value,
            token:token
        };

        Websocket.send(JSON.stringify(json));
        return false;
    }
</script>

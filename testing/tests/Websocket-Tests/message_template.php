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
        <option value="send_message">Send Message</option>
        <option value="send_file">Send File</option>
        <option value="other">Other</option>
    </select>
    <input name="user" type="text" placeholder="username">
    <input name="text" type="text" placeholder="message">
    <button>Submit</button>
</form>

<script>
    Websocket = null;
    window.addEventListener("load", function () {
        var url = "ws://localhost:8000/rooms/uure93";
        Websocket = new WebSocket(url);
        Websocket.onopen = function(){
        };
        Websocket.onmessage = function(evt){
            console.log(evt.message);
        };
    })

    function websock_submit(event, form){
        event.preventDefault();
        event.stopPropagation();

        var form = document.getElementsByTagName('form')[0];
        console.log(form.action.value);

        var json = {action:form.action.value,
                    user:form.user.value,
                    message:form.text.value};

        Websocket.send(JSON.stringify(json));
        return false;
    }


</script>

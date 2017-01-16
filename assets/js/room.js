/**
 * Created by ian on 11/19/16.
 */

window.addEventListener("load", function () {
    Chat.init();
    Modal.init();
    Resource.load("/assets/php/components/modal/room_settings.php", "Settings", InitSettingsModal);
    Room.connect();
    window.document.title = Room.data.RoomName;
    document.getElementById("r-title").innerHTML = Room.data.RoomName;
    repopulateMessages();
});

var Chat = {
    chatlog:null,
    init:function(){
        Chat.chatlog = document.getElementById("chat");

    },
    send:function () {
        
    }
};

var Room = {
    data:null,
    socket:null,
    connected:false,
    connect:function(){
        if(!Room.data) return;
        var url = "wss:dev.slingapp.net/rooms/";
        if(window.location.host === "localhost"){
            url = "ws:localhost:8001/rooms/";
        }
        url += Room.data.RoomID;

        console.log("Attempting to connect to ", url);
        Room.socket = new WebSocket(url);

        Room.socket.onopen = function(){
            Room.socket.send(JSON.stringify({
                action:"Register",
                token:Account.data.LoginToken,
                room:Room.data.ID
            }));
            Room.connected = true;
        };
        Room.socket.onmessage = function(data){
            var message = JSON.parse(data.data);
            console.log(message);
            if(message.notify){
                Toast.pop(textNode(message.notify),3000);
            }
            var type = message.type;
            switch (type){
                case "Message":
                {
                    var text = message.text;
                    var sender = message.sender;
                    putMessage(sender, text);

                }break;

                case "Participant Joined":
                {
                    var accountID = message.id;
                    var sn = message.nick;
                    Room.data.Accounts[accountID] = {ScreenName:sn, ID:accountID};
                    updateUsersHere();
                }break;

                case "Confirmation":
                {
                    if (message.success === false){
                        Toast.error(textNode("Action: " + message.action + " failed"));
                    }
                }break;

                default:{
                    console.info(message);
                }

            };
        };
        Room.socket.onerror = function (error) {
            Toast.error(textNode("Room Connection Error"));
            console.error(error);
        }
        setTimeout(function(){
            if(!Room.connected){
                Toast.error(textNode("Could not connect to Room"));
            }
        }, 5000);
    },
    send:function (json) {
        if(Room.connected){
            Room.socket.send(JSON.stringify(json));
        }else{
            Room.connect();
        }
    },
    sendMessage:function (message) {
        if(message.length <= 2000){
            var json = {action:"Send Message",
                token:Account.data.LoginToken,
                text:message};

            Room.send(json);
        }else{
            alert("That message is too big!  Limit your messages to 2000 characters.");
        }
    },
    getRoomCodes:function(){
        return Room.data.RoomCodes;
    },
    createRoomCode:function (uses, expires) {
        Room.socket.send(JSON.stringify({
            action:"Create Room Code",
            token:Account.data.LoginToken,
            uses:uses,
            expires:expires
        }));
    },
    settings:{
        categoryPanel:{
            links:null,
            node:null
        },
        optionsPanel:{
            panels:null,
            node:null
        }
    }

};

var Account = {
    data:null,
    login:function(){
        var token = GetToken();
        console.log(token);
        $.ajax({
            type: 'post',
            url: '/assets/php/components/account2.php',
            dataType: 'JSON',
            data: {
                action:"login",
                token:token
            },
            success: function (data) {
                console.log(data);
            },
            error: function (error) {
                console.log(error);
            }
        });
    }
};


function showSettings(){
    Modal.create("Settings", "darken");
}
function leaveRoom(){
    window.location.replace("http://localhost")
}

function leaveRoom() {
    window.location = "/"

}

function InitSettingsModal(){
    Room.settings.categoryPanel.node = Resource.dictionary["Settings"].querySelector(".settings-left");
    Room.settings.optionsPanel = Resource.dictionary["Settings"].querySelector(".settings-right");
    Room.settings.categoryPanel.links = Room.settings.categoryPanel.node.querySelectorAll("a");
    Room.settings.optionsPanel.panels = Room.settings.optionsPanel.querySelectorAll(".settings-panel");

    Room.settings.categoryPanel.links.forEach(function (l) {
        l.addEventListener("click", function () {
            Room.settings.optionsPanel.panels.forEach(function (p) {
                p.removeClass("active");
            });
            Room.settings.categoryPanel.links.forEach(function (l) {
                l.removeClass("selected");
            });
            l.className+= " selected";
            var id = l.getAttribute("href").slice(1);
            document.getElementById(id).className += " active";
        });
    });

    updateUsersHere();
    updateInvites();
}

function updateUsersHere(){
    var userPanel = Room.settings.optionsPanel.querySelector("#Users");
    var here = userPanel.querySelector("#users-here");
    var you = userPanel.querySelector("#you");
    here.innerHTML = "";
    var loggedInUserID = Account.data.ID;
    console.log(loggedInUserID);
    you.innerHTML = "<span class='user'>" + Room.data.Accounts[loggedInUserID].ScreenName + "</span><br>" + you.innerHTML;
    for(var key in Room.data.Accounts){
        if(Room.data.Accounts.hasOwnProperty(key)){
            var account = Room.data.Accounts[key];
            if(account.ID != loggedInUserID)
                here.innerHTML += "<span class='user'>" + account.ScreenName + "</span><br>";
        }
    }
}

function createInviteCode(e){
    var roomid = Room.data.RoomID;
    var token = GetToken();
    $.ajax({
        type: 'post',
        url: '/assets/php/components/room.php',
        dataType: 'JSON',
        data: {
            action: "gencode",
            room: roomid,
            token: token
        },
        success: function (data) {
            Room.data.RoomCodes[data.Code] = data;
            updateInvites();
        },
        error: function (error) {
            console.log(error);
        }
    });
}
function updateInvites(){
    var invitepanel = Room.settings.optionsPanel.querySelector("#Invites");
    var iCodeDiv = invitepanel.querySelector("#invite-codes");
    iCodeDiv.innerHTML = "";
    for(var code in Room.data.RoomCodes){
        if(Room.data.RoomCodes.hasOwnProperty(code)){
            var rc = Room.data.RoomCodes[code];
            var tr = document.createElement("tr");
            tr.innerHTML += "<td><input class='form-control iv-code' onclick='this.select()' readonly value='" + rc.Code + "'></td>";
            tr.innerHTML += "<td>" + Room.data.Accounts[rc.Creator].ScreenName + "</td>";
            tr.innerHTML += "<td>" + rc.Expires + "</td>";
            iCodeDiv.appendChild(tr);
        }
    }
}

function changeScreenName(){
    var token = GetToken();
    var name = prompt("Enter a new nickname:");
    $.ajax({
        type: 'post',
        url: '/assets/php/components/room.php',
        dataType: 'JSON',
        data: {
            action: "changename",
            room: roomid,
            token: token,
            name: name
        },
        success: function (data) {
            Room.data.RoomCodes.push(data);
            updateInvites();
        },
        error: function (error) {
            console.log(error);
        }
    });
}

function textNode(msg){
    var text = document.createElement("text");
    text.innerHTML = msg;
    return text;
}

function snFromAccountID(id){
    var len = Room.data.Accounts.length;
    for(var i = 0; i<len; i++){
        if(Room.data.Accounts[i].ParticipantID == id){
            return Room.data.Accounts[i].ScreenName;
        }
    }
}

function sendMessage(){
    var tarea = document.getElementById("send-box").querySelector("input");
    var text = tarea.value;
    if (text != "")
        Room.sendMessage(text);
    tarea.focus();
    tarea.value = "";

}

function updateScroll(){
    var element = document.getElementById("chat-log");
    element.scrollTop = element.scrollHeight;
}

function putMessage(sender, text, before){
    console.log(text);
    text = text.replace(/(https?:[/][/])?([a-zA-Z-]+[.][a-z]+)/, "<a href='http://$2'>$1$2</a>");
    var messageLog = document.getElementById("chat-log");
    var username = Room.data.Accounts[sender].ScreenName;
    var message = document.createElement("div");
    if (sender == Account.data.ID){
        message.className = "message mine";
        username += " (you)";
    }
    else {
        message.className = "message";
    }
    message.innerHTML = "<span class='user'>"+ username +"</span><br><span class='message-text'>" + text + "</span>";
    if (before){
        messageLog.insertBefore(message, messageLog.firstChild);
    }else{
        messageLog.appendChild(message);
    }
    updateScroll();
}

function repopulateMessages() {

    var before = false;
    for(var key in Messages){
        if(Messages.hasOwnProperty(key)){
            var sender = Messages[key].author;
            var text = Messages[key].content;
            putMessage(sender, text, before);
            before = true;
        }
    }

}

function openInvites() {
    Modal.create("Settings", "darken");
    document.getElementById("InvitesLink").click();
}

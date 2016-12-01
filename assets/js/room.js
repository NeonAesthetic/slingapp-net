/**
 * Created by ian on 11/19/16.
 */

window.addEventListener("load", function () {
    Chat.init();
    Modal.init();
    Resource.load("/assets/php/components/modal/room_settings.php", "Settings", InitSettingsModal);
    Room.connect();
    window.document.title = Room.data.RoomName;
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
        var url = "ws:localhost:8001/rooms/" + Room.data.RoomID;
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
            console.info(data.data);
            var message = JSON.parse(data.data);
            console.log(message);
            if(message.notify){
                Toast.pop(textNode(message.notify),3000);
            }
            var type = message.Type;
            switch (type){
                case "Message":
                {
                    var text = message.text;
                    var sender = message.sender;
                    var messageLog = document.getElementById("chat-log");
                    messageLog.innerHTML += text + "<br>";
                }break;
                default:

            };
        };
        Room.socket.onerror = function (error) {
            Toast.error(textNode("Room Connection Error"));
            console.error(error);
        }
        // setTimeout(function(){
        //     if(!Room.connected){
        //         Toast.error(textNode("Could not connect to Room"));
        //     }
        // }, 5000);
    },
    sendMessage:function (message) {
        var json = {action:"Send Message",
                    token:Account.data.LoginToken,
                    text:message};
        var mes = JSON.stringify(json);
        console.log(mes);
        Room.socket.send(mes);
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

function showSettings(){
    Modal.create("Settings", "darken");
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
            alert(data.Code);
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
    var accountID = Account.data.ID;
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
    var text = document.getElementById("send-box").querySelector("textarea").value;
    console.log(text);
    Room.sendMessage(text);
}


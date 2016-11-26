/**
 * Created by ian on 11/19/16.
 */

window.addEventListener("load", function () {
    Chat.init();
    Modal.init();
    Resource.load("/assets/php/components/modal/room_settings.php", "Settings", InitSettingsModal);
    Room.connect();

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
    connect:function(){
        if(!Room.data) return;
        var url = "ws:localhost:8001/rooms/" + Room.data.RoomID;
        console.log("Attempting to connect to ", url);
        try{
            Room.socket = new WebSocket(url);
        }catch(e){
            Toast.error(textNode(e));
            console.error("wwwww");
        }


        Room.socket.onopen = function(){
            Room.socket.send(JSON.stringify({
                action:"Register",
                token:Account.data.LoginToken,
                room:Room.data.ID
            }));
        };
        Room.socket.onmessage = function(data){
            var message = JSON.parse(data.data);
            console.log(message);
            if(message.notify){
                Toast.pop(textNode(message.notify),3000);
            }
            var type = message.Type;
            switch (type){
                case "join":
                {
                    var name = message.name;
                }break;
                default:

            };
        };
        Room.socket.onerror = function (error) {
            Toast.error(textNode(error));
        }
        setTimeout(function(){}, 5000);
    },
    sendMessage:function (message) {
        var json = {action:"Send Message",
                    token:Account.data.LoginToken,
                    text:message};
        Room.socket.send(JSON.stringify(json));
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
    here.innerHTML = "";
    var num_users = Room.data.Accounts.length;
    for(var i = 0; i<num_users; i++){
        here.innerHTML += "<span class='user'>" + Room.data.Accounts[i].ScreenName + "</span><br>";
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
            Room.data.RoomCodes.push(data);
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
    var num_codes = Room.data.RoomCodes.length;
    for(var i = 0; i<num_codes; i++){
        var tr = document.createElement("tr");
        tr.innerHTML += "<td>" + Room.data.RoomCodes[i].Code + "</td>";
        tr.innerHTML += "<td>" + snFromAccountID(Room.data.RoomCodes[i].Creator) + "</td>";
        tr.innerHTML += "<td>" + Room.data.RoomCodes[i].Expires + "</td>";
        iCodeDiv.appendChild(tr);
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



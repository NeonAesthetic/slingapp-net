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
        var url = "ws:localhost:8000/rooms/" + Room.data.RoomID;
        console.log("Attempting to connect to ", url);
        Room.socket = new WebSocket(url);
        Room.socket.onopen = function(){
            Room.socket.send(JSON.stringify({
                action:"register",
                token:Account.data.LoginToken
            }));
        };
        Room.socket.onmessage = function(data){
            var message = JSON.parse(data.data);
            var type = message.type;
            switch (type){
                case "chat":
                {
                    var text = message.text;
                    var sender = message.sender;
                }break;

                case "join":
                {
                    var name = message.name;
                }break;
                default:
                    console.error(message);
            };
        }
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

function displayRoomCodes(){
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
            room:roomid,
            token: token
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
function updateInvites(){

}


/**
 * Created by ian on 11/19/16.
 */

window.addEventListener("load", function () {
    Chat.init();
    Modal.init();
    Resource.load("/assets/php/components/modal/room_codes.php", "Room Codes");
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
    }
};

function displayRoomCodes(){
    Modal.create("Room Codes", "darken");
    document.getElementById('room-codes').innerHTML = Room.data;
    console.log(Room.data);
}


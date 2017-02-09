/**
 * Created by Ian Murphy on 2/7/2017.
 */
if ( ! "Peer" in window ){
    console.error("peer.js required to use this library");
};

var MEDIA = {
    AUDIO : {audio: true, video:false},
    VIDEO : {audio: false, video:true},
    BOTH  : {audio: true, video:true}
}

var AVC = {
    connection:null,
    id:null,
    options:{key: 'lwjd5qra8257b9'},
    audioSources:[],
    connected:false,
    connectButton:null,
    audioInput:null,
    getUserMedia:function(mediaType, callback){
        if(!callback){
            console.error("Must provide a callback function in AVC.getUserMedia");
            return false;
        }
        if(!AVC.audioInput){
            navigator.getUserMedia(mediaType, function(stream){
                AVC.audioInput = stream;
                callback(stream);
            }, function (error) {
                console.error("IT's BORKED: ", error);

            })
        }else{
            callback(AVC.audioInput);
        }

    },
    connectVoice:function(){
        if(!AVC.connection){
            AVC.connection = new Peer(AVC.id, AVC.options);

            AVC.connection.on("open", function (id) {
                console.log("PeerID is " + id);
            });

            AVC.connection.on("call", AVC.acceptPeerAudioStream);

            AVC.connection.on("error", function (error) {
                if (error.type == "peer-unavailable"){
                    console.log("Peer could not be found.");
                }
            })
        }else{
            AVC.connection.reconnect();
        }


        var accounts = Room.data.Accounts;
        var len = accounts.length;
        console.log("Attempting to connect voice");
        for(var peerid in accounts){
            if(accounts.hasOwnProperty(peerid) && peerid != Account.data.ID){
                console.log("Connecting to peer with id: " + peerid);
                AVC.getPeerAudioStream(peerid, function (stream) {
                    AVC.createPeerAudioNode(stream);
                });
            }
        }
        AVC.connected = true;
        AVC.connectButton.innerHTML = "DISCONNECT VOICE";
        AVC.connectButton.classList.add("button-green");
        AVC.connectButton.onclick = function () {
            AVC.disconnectVoice();
        }
    },
    disconnectVoice:function(){
        AVC.connection.destroy();
        AVC.connection = null;
        console.log("Disconnecting");
        AVC.connectButton.innerHTML = "CONNECT VOICE";
        AVC.connectButton.classList.remove("button-green");
        AVC.connectButton.onclick = function () {
            AVC.connectVoice();
        }
    },
    getPeerAudioStream:function (peerid, callback) {
        AVC.createAudioStream(function (stream) {
            var call = AVC.connection.call(peerid, stream);
            call.on("stream", function(stream){
                callback(stream);
            });
        });

    },
    createAudioStream:function(callback){
        AVC.getUserMedia(MEDIA.AUDIO, callback);
    },
    acceptPeerAudioStream:function (call) {
        console.log("Client attempting to connect");
        if (AVC.connected){
            AVC.createAudioStream(function (stream) {
                call.answer(stream);
                call.on("stream", function (stream) {
                    AVC.createPeerAudioNode(stream)
                });
            });
        }
    },
    createPeerAudioNode:function (stream) {
        var audioNode = createAudioSourceNode();
        audioNode.src = URL.createObjectURL(stream);
        document.body.appendChild(audioNode);
        AVC.audioSources.push(audioNode);
        // audioNode.peer = peerid;
    }
}


window.addEventListener("load", function(){
    AVC.connectButton = document.getElementById("connect-voice");

});

function createAudioSourceNode(){
    var node = document.createElement("audio");
    node.autoplay = true;
    return node;
}


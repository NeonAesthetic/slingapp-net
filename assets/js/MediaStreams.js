/**
 * Created by Ian Murphy on 2/7/2017.
 */
if ( ! "Peer" in window ){
    console.error("peer.js required to use this library");
};



var MEDIA = {

    AUDIO  : {audio: true, video:false},
    VIDEO  : {audio: false, video:true},
    BOTH   : {audio: true, video:true},
    SCREEN : {
        audio: false,
        video: {
            mandatory: {
                chromeMediaSource: 'desktop',
                chromeMediaSourceId: null,
                minWidth: 1280,
                maxWidth: 1920,
                minHeight: 720,
                maxHeight: 1080,
                minFrameRate:60,
                maxFrameRate:60
            }
        },
        options:[]
    }
};

window.addEventListener("load", function(){
    AVC.peerVideoStream = new MediaStream();
    AVC.videoConnection = AVC.connectToPeerServer(AVC.id + "v", AVC.videoConnection, AVC.acceptPeerVideoStream);
    AVC.getAllPeerVideo(AVC.peerVideoStream);
})


var AVC = {
    audioConnection:null,
    videoConnection:null,
    id:null,
    peerAudioID:null,
    peerVideoID:null,
    options:{
        host: 'slingapp.net',
        port: 9000,
        secure: true,
        iceServers:[
            { url: 'stun:stun.ekiga.net' },
            { url: 'stun:stun1.l.google.com:19302' },
            { url: 'stun:stun2.l.google.com:19302' },
            { url: 'stun:stun3.l.google.com:19302' }
        ]
    },
    audioSources:[],
    videoSources:[],
    peerVideoStream:null,
    audioConnected:false,
    connectButton:null,
    audioInput:null,
    getUserMedia:function(mediaType, callback){
        if(!callback){
            console.error("Must provide a callback function in AVC.getUserMedia");
            return false;
        }
        if(!AVC.audioInput){
            navigator.webkitGetUserMedia(mediaType, function(stream){
                callback(stream);
            }, function (error) {
                console.error("IT's BORKED: ", error);

            })
        }else{
            if(mediaType == MEDIA.AUDIO)
                callback(AVC.audioInput);
            else{
                callback(AVC.peerVideoStream);
            }
        }

    },
    connectToPeerServer:function (peer_id, connection, on_call) {
        if(!connection){
            connection = new Peer(peer_id, AVC.options);

            connection.on("open", function (id) {
                console.log("PeerID is " + id);
            });

            connection.on("call", on_call);

            connection.on("error", function (error) {
                if (error.type == "peer-unavailable"){
                    console.log("Peer could not be found.");
                }
            })
        }else{
            connection.reconnect();
        }
        return connection;
    },
    connectVoice:function(){
        if(!AVC.audioConnection){
            AVC.peerAudioID = AVC.id + "a";
            AVC.audioConnection = new Peer(AVC.peerAudioID, AVC.options);

            AVC.audioConnection.on("open", function (id) {
                console.log("PeerID is " + id);
            });

            AVC.audioConnection.on("call", AVC.acceptPeerAudioStream);

            AVC.audioConnection.on("error", function (error) {
                if (error.type == "peer-unavailable"){
                    console.log("Peer could not be found.");
                }
            })
        }else{
            AVC.audioConnection.reconnect();
        }


        var accounts = Room.data.Accounts;
        var len = accounts.length;
        console.log("Attempting to connect voice");
        for(var peerid in accounts){
            if(accounts.hasOwnProperty(peerid) && peerid != Account.data.ID){
                console.log("Connecting to peer with id: " + peerid);
                AVC.getPeerAudioStream(peerid + "a", function (stream) {
                    AVC.createPeerAudioNode(stream);
                });
            }
        }
        AVC.audioConnected = true;
        AVC.connectButton.innerHTML = "DISCONNECT VOICE";
        AVC.connectButton.classList.add("button-green");
        AVC.connectButton.onclick = function () {
            AVC.disconnectVoice();
        }
    },
    disconnectVoice:function(){
        AVC.audioConnection.destroy();
        AVC.audioConnection = null;
        console.log("Disconnecting");
        AVC.connectButton.innerHTML = "CONNECT VOICE";
        AVC.connectButton.classList.remove("button-green");
        AVC.connectButton.onclick = function () {
            AVC.connectVoice();
        }
    },

    getPeerAudioStream:function (peerid, callback) {
        AVC.createAudioStream(function (stream) {
            var call = AVC.audioConnection.call(peerid, stream);
            call.on("stream", function(stream){
                callback(stream);
            });
        });

    },
    getPeerVideoStream:function (peerid, callback) {
            var call = AVC.videoConnection.call(peerid, AVC.peerVideoStream);
            call.on("stream", function(stream){
                callback(stream);
            });


    },
    createAudioStream:function(callback){
        AVC.getUserMedia(MEDIA.AUDIO, callback);
    },
    acceptPeerAudioStream:function (call) {
        console.log("Client attempting to connect");
        if (AVC.audioConnected){
            AVC.createAudioStream(function (stream) {
                call.answer(stream);
                call.on("stream", function (stream) {
                    AVC.createPeerAudioNode(stream)
                });
            });
        }
    },
    acceptPeerVideoStream:function(call){


        console.log("Peer attempting to connect");
        call.answer(AVC.peerVideoStream);
        call.on("stream", function (stream) {
            console.log(stream);
            AVC.setPeerVideoNode(call.peer.slice(0,-1), stream);
        });


    },
    createPeerAudioNode:function (stream) {
        var audioNode = createAudioSourceNode();
        audioNode.src = URL.createObjectURL(stream);
        document.body.appendChild(audioNode);
        AVC.audioSources.push(audioNode);
        // audioNode.peer = peerid;

    },
    createPeerVideoNode:function (stream) {
        var videoNode = createVideoSourceNode();
        videoNode.src = URL.createObjectURL(stream);
        videoNode.className = 'user-preview';
        return videoNode;
    },
    connectScreenCapture:function () {
        AVC.getUserScreencaptureStream(function(stream){
            AVC.getAllPeerVideo(stream);
        });

    },
    getUserScreencaptureStream:function (callback) {

        var extensionID = "kfgaafajpkopkaljlblgmijmedhcbhkm";
        chrome.runtime.sendMessage(extensionID, {text: "wew lad"}, function(response) {
            if(response){
                var options = MEDIA.SCREEN;
                options.video.mandatory.chromeMediaSourceId = response.media_id;
                AVC.getUserMedia(options, callback);
            }else{
                Toast.error(textNode("Extension not installed"));
            }
        });


    },
    createUserPreviewNode:function (stream) {
        var node = createVideoSourceNode();
        node.src = window.URL.createObjectURL(stream);
        node.className = "user-preview";
        return node;
    },
    setPeerVideoNode:function(id, stream){
        AVC.peerVideoStream = stream;
        var node = AVC.createPeerVideoNode(stream);
        var videoDiv = document.getElementById("NU" + id);
        videoDiv.innerHTML = "";
        videoDiv.appendChild(node);
        // console.log(node);
    },
    getAllPeerVideo:function(stream){
        AVC.peerVideoStream = stream;
        AVC.setPeerVideoNode(AVC.id, stream);

        var accounts = Room.data.Accounts;
        var len = accounts.length;
        console.log("Attempting to connect video");
        for(var peerid in accounts){
            if(accounts.hasOwnProperty(peerid) && peerid != Account.data.ID){
                console.log("Connecting to peer with id: " + peerid);
                AVC.getPeerVideoStream(peerid + "v", function (stream) {
                    var id = peerid;
                    AVC.setPeerVideoNode(id, stream);
                });
            }
        }
        AVC.videoConnected = true;
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


function createVideoSourceNode(){
    var node = document.createElement("video");
    node.autoplay = true;
    node.addEventListener("dblclick", function (event) {
        this.webkitRequestFullscreen();
    });
    node.controls = false;
    return node;
}


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

});


var AVC = {
    audioConnection:null,
    videoConnection:null,
    id:null,
    peerAudioID:null,
    peerVideoID:null,
    options:{
        host: 'slingapp.net',
        port: 9000,
        secure: true

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
                AVC.getAllPeerVideo(AVC.peerVideoStream);
            });

            connection.on("call", on_call, null);



            connection.on("error", function (error) {
                if (error.type == "peer-unavailable"){
                    console.log("Peer could not be found.");
                }else
                console.error(error);
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
        console.log("Get Peer Video Stream: ", peerid);
        var call = AVC.videoConnection.call(peerid, AVC.peerVideoStream);
        call.on("stream", callback);
        call.on("close", function () {
            console.info("Close");
            var video = $('#video-' + call.peer)[0];

            $('.ui.sidebar .accordion').removeChild(video.divTitle);
            $('.ui.sidebar .accordion').removeChild(video.divContent);
            $('.ui.sidebar .accordion').removeChild(video);
        });
        call.on("error", function (err) {
            console.error(err);
        })
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
        console.log(AVC.peerVideoStream);
        call.answer(AVC.peerVideoStream);
        call.on("stream", function (stream) {
            console.log(stream);
            console.log("Accepted Peer Media Stream");
            AVC.setPeerVideoNode(call.peer.slice(0,-1), stream);
        });
        call.on("error", function (error) {
            console.log(error);
        });
        call.on("close", function () {
            var id = call.peer.slice(0,-1);
            console.info("Disconnect: ", id);
            var video = $('#video-' + id)[0];

            var accordion = $('.ui.sidebar .accordion')[0];
            accordion.removeChild(video.divTitle);
            accordion.removeChild(video.divContent);
        });


    },
    createPeerAudioNode:function (stream) {
        var audioNode = createAudioSourceNode();
        audioNode.srcObject = stream;
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
    connectScreenCapture:function (callback) {
        AVC.getUserScreencaptureStream(function(stream){

            AVC.peerVideoStream = stream;
            console.log("CAPTURE:", stream);
            AVC.getAllPeerVideo(stream);
            if(callback) callback(true);
        });

    },
    getUserScreencaptureStream:function (callback) {

        var extensionID = "pecpbndlndfegjibmbaplkjdkhdgmnei";
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
    setPeerVideoNode:function(id, stream){
        var video = document.getElementById("video-" + id);
        if(!video){

            var title = document.createElement("div");
            var content = document.createElement("div");
            video = document.createElement("video");
            var sn = snFromId(id);
            if (id == Account.data.ID){
                sn = "<span class='user mine uid-" + id + "'>" + sn + "</span><a id='video-chat-status' style='float:right;'></a> ";
            }
            title.className = "title";
            title.innerHTML = "<i class='dropdown icon'></i>" + sn + "</div>";
            content.className = "content";
            content.style.width = "auto";

            video.setAttribute("id", "video-" + id);
            // video.setAttribute("height", 170);
            // video.setAttribute("width", 235);
            video.style.background = "#292929";
            video.style.border = "1px solid #333";
            video.style.maxHeight = "calc(100vh - 80px)";
            video.style.maxWidth = "100%";

            video.ondblclick = function(){
                this.webkitRequestFullScreen();
            };

            video.divTitle = title;
            video.divContent = content;


            content.appendChild(video);

            var wrapper = document.createElement("div");
            
            wrapper.appendChild(title);
            wrapper.appendChild(content);

            $('.ui.sidebar .accordion').append(wrapper);
            // $('.ui.sidebar .accordion').append(content);
        }


        video.srcObject = stream;
        video.autoplay=true;
    },
    getAllPeerVideo:function(stream){
        // AVC.peerVideoStream = stream;
        AVC.setPeerVideoNode(AVC.id, stream);
        AVC.button.checkStatus();

        var accounts = Room.data.Accounts;
        console.log("Attempting to connect video");
        for(var peerid in accounts){
            if(accounts.hasOwnProperty(peerid) && peerid != Account.data.ID){
                console.log("Connecting to peer with id: " + peerid);
                AVC.getPeerVideoStream(peerid + "v", function (stream) {
                    var id = peerid;
                    console.log('got peer video');
                    AVC.setPeerVideoNode(id, stream);
                });
            }
        }
        AVC.videoConnected = true;
    },
    disconnectVideo:function () {
        if(AVC.peerVideoStream){
            AVC.peerVideoStream.getTracks()[0].stop();
            AVC.peerVideoStream = null;
            AVC.button.checkStatus();
        }
    },
    button:{
        stopStream:function (event) {
            event.preventDefault();
            event.stopPropagation();
            AVC.disconnectVideo();
            // if(!AVC.peerVideoStream){
            //     AVC.button.setStatus("OFF", "#ff7777", "Start Streaming", AVC.button.startStream);
            // }
        },
        startStream:function (event) {
            event.preventDefault();
            event.stopPropagation();
            AVC.connectScreenCapture(function (streaming) {
                // if (streaming) {
                //     AVC.button.setStatus("ON", "#77ff77", "Stop Streaming", AVC.button.stopStream);
                // }
            });
        },
        setStatus:function (text, color, tooltip, onclick) {
            var status = document.getElementById("video-chat-status");
            status.innerHTML = text;
            status.setAttribute("data-tooltip", tooltip);
            status.style.color = color;
            status.onclick = onclick;
        },
        checkStatus:function () {
            if(AVC.peerVideoStream && AVC.peerVideoStream.active){
                AVC.button.setStatus("Stream ON", "#77ff77", "Stop Streaming", AVC.button.stopStream);
            }else{
                AVC.button.setStatus("Stream OFF", "#ff7777", "Start Streaming", AVC.button.startStream);
            }
        }
    }
};





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

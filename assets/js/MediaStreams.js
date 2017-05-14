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
    AVC.connectVoice();
    AVC.BASE_VIDEO_NODE = document.getElementById("preview-"+AVC.id).cloneNode(true);
    var $bn = $(AVC.BASE_VIDEO_NODE);
    $bn.find('.mine')[0].classList.remove("mine");
    $bn.find('.button').unbind("click");
    AVC.BASE_VIDEO_NODE = $bn[0];

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
    audioCalls:{},
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
            AVC.audioConnection = new Peer(AVC.id + "a", AVC.options);
            AVC.audioConnection.on("open", function (id) {
                AVC.button.checkStatus();
                console.log("Peer Audio ID is " + id);
            });

            AVC.audioConnection.on("call", AVC.acceptPeerAudioStream);

            AVC.audioConnection.on("error", function (error) {
                if (error.type == "peer-unavailable"){
                    console.log("Peer could not be found.");
                }
            })
        }else if(AVC.audioConnection.disconnected){
            AVC.audioConnection.reconnect();
        }else{
            return
        }




    },
    leaveVoiceChannel:function(){
        if(AVC.audioConnection){
            for(var peerid in AVC.audioCalls){
                if(AVC.audioCalls.hasOwnProperty(peerid)){
                    console.log(peerid);
                    var call = AVC.audioCalls[peerid];
                    console.log("Close call to peer " + call.peer);
                    call.close();
                }
            }
            AVC.audioCalls = [];
            AVC.audioConnected = false;
        }
        AVC.button.checkStatus();
    },
    enterVoiceChannel:function(){
        var accounts = Room.data.Accounts;
        AVC.audioConnected = true;
        console.log("Attempting to connect voice");
        for(var peerid in accounts){
            if(accounts.hasOwnProperty(peerid) && peerid != Account.data.ID){
                console.log("Connecting to peer with id: " + peerid);
                AVC.getPeerAudioStream(peerid + "a", function (stream) {
                    AVC.setUserStatus(peerid, true, null);
                });
            }
        }
        AVC.button.checkStatus();
    },
    getPeerAudioStream:function (peerid, callback) {
        AVC.createAudioStream(function (stream) {
            var call = AVC.audioConnection.call(peerid, stream);
            call.on("stream", function(stream){
                var peerid = call.peer.slice(0,-1);
                AVC.setUserStatus(peerid, true, null);
                call.node = AVC.createPeerAudioNode(stream);
                AVC.audioCalls[call.peer] = call;
            });
            call.on("close", function () {
                console.log("Peer closed connection");
                AVC.setUserStatus(call.peer.slice(0,-1), false, null);
                console.log("GPAS on close rem");
                document.body.removeChild(call.node);
                delete AVC.audioCalls[call.peer];
            });

        });

    },
    getPeerVideoStream:function (peerid, callback) {
        var call = AVC.videoConnection.call(peerid, AVC.peerVideoStream);
        call.on("stream", function (stream) {
            AVC.setUserStatus(peerid, false, stream.active);
            callback(stream);
        });
        call.on("close", function () {
            console.info("Close");
            var preview = $('#preview-' + call.peer)[0];

            $('.ui.sidebar .accordion').removeChild(preview);
        });
        call.on("error", function (err) {
            console.error(err);
        })
    },
    createAudioStream:function(callback){
        AVC.getUserMedia(MEDIA.AUDIO, callback);
    },
    acceptPeerAudioStream:function (call) {
        console.log("Recieving RTC Call - Audio");
        AVC.setUserStatus(call.peer.slice(0,-1), true, null);
        if (AVC.audioConnected){
            AVC.createAudioStream(function (stream) {
                call.answer(stream);
                call.on("stream", function (stream) {
                    var peerid = call.peer.slice(0,-1);
                    AVC.setUserStatus(peerid, true, null);
                    call.node = AVC.createPeerAudioNode(stream);
                });
                call.on("close", function () {
                    console.log("Peer closed connection");
                    AVC.setUserStatus(call.peer.slice(0,-1), false, null);
                    document.body.removeChild(call.node);
                    delete AVC.audioCalls[call.peer];
                });
                AVC.audioCalls[call.peer] = call;
            });
        }
    },
    acceptPeerVideoStream:function(call){
        AVC.getUserPreviewNode(call.peer.slice(0,-1));
        call.answer(AVC.peerVideoStream);
        call.on("stream", function (stream) {
            AVC.setPeerVideoNode(call.peer.slice(0,-1), stream);
        });
        call.on("error", function (error) {
            console.log(error);
        });
        call.on("close", function () {
            var id = call.peer.slice(0,-1);
            console.info("Disconnect: ", id);
            var preview = $('#preview-' + id)[0];

            var accordion = $('.ui.sidebar .accordion')[0];
            accordion.removeChild(preview);
        });


    },
    createPeerAudioNode:function (stream) {
        var audioNode = createAudioSourceNode();
        audioNode.srcObject = stream;
        document.body.appendChild(audioNode);
        console.log("appending audio node");
        return audioNode;
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
        var userPreviewNode = AVC.getUserPreviewNode(id);
        AVC.setUserStatus(id, false, stream.active);
        var video = $(userPreviewNode).find('video')[0];

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
                    AVC.getUserPreviewNode(id);
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
        checkStatus:function () {
            var audio = false;
            var video = false;
            video = (AVC.peerVideoStream && AVC.peerVideoStream.active);
            audio = AVC.audioConnected;

            AVC.setUserStatus(AVC.id, audio, video);
        }
    },
    getUserPreviewNode:function(id){
        console.log("Create node for ", id);
        var node = document.getElementById("preview-" + id);
        if(!node){

            node = AVC.BASE_VIDEO_NODE.cloneNode(true);

            node.setAttribute('id', "preview-" + id);
            var name = node.querySelector('.title').querySelector('.user');
            name.className = "user uid-" + id;
            name.innerHTML = snFromId(id);

            $('.ui.sidebar .accordion').append(node);

        }
        return node;
    },
    setUserStatus:function (id, audio, video) {
        var userPreview = AVC.getUserPreviewNode(id);
        console.log($(userPreview).find('.audio-status')[0]);
        var audioStatus = $(userPreview).find('.audio-status')[0];
        var videoStatus = $(userPreview).find('.video-status')[0];
        if (audio != null){
            if(audio){
                audioStatus.classList.remove("red");
                audioStatus.classList.add("green");
                audioStatus.setAttribute("data-tooltip", "Connected");
                if(id == Account.data.ID)
                    audioStatus.onclick = function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        AVC.leaveVoiceChannel();
                    }
            }else{
                audioStatus.classList.remove("green");
                audioStatus.classList.add("red");
                audioStatus.setAttribute("data-tooltip", "Disconnected.");
                if(id == Account.data.ID)
                    audioStatus.onclick = function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        AVC.enterVoiceChannel();
                    }
            }
        }

        if(video != null){
            if(video){
                videoStatus.classList.remove("red");
                videoStatus.classList.add("green");
                videoStatus.setAttribute("data-tooltip", "Sharing");
                if(id == Account.data.ID)
                    videoStatus.onclick = function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        AVC.disconnectVideo();
                    }
            }else{
                videoStatus.classList.remove("green");
                videoStatus.classList.add("red");
                videoStatus.setAttribute("data-tooltip", "Not Sharing");
                if(id == Account.data.ID)
                    videoStatus.onclick = function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        AVC.connectScreenCapture();
                    }
            }
        }

    },
    events:{
        peerCloseAudioConnection:function(){

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

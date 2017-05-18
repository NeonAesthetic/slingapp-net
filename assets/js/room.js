/**
 * Created by ian on 11/19/16.
 */
function textNode(msg) {
    var text = document.createElement("text");
    text.innerHTML = msg;
    return text;
}

$(document).ready(function () {
    window.document.title = Room.data.RoomName;
    document.getElementById("r-title").innerHTML = Room.data.RoomName;

    Room.connect();
    Room.SetupSemanticUI();
    Chat.init();
    Chat.repopulateMessages();
    Chat.initDragDrop();
    Chat.updateScroll();
    RoomCookies.checkInVol();
    RoomCookies.checkOutVol();

    if((RoomCookies.getCookie("theme") === ""))
        RoomCookies.setCookie("theme", "dark");

    for (var key in Room.data.RoomCodes) {
        if (Room.data.RoomCodes.hasOwnProperty(key)) {
            var code = Room.data.RoomCodes[key];
            Settings.appendInviteCode(code);
        }
    }
    var thumbnails = document.getElementById("video-thumbnails");
    var videoContainer = document.getElementById("video-container");
    Sortable.create(thumbnails, {
        group: "videos", onAdd: function (event) {
            console.log(event);
            var video = event.item.querySelector('video');
            video.play();
        }
    });
    Sortable.create(videoContainer, {
        group: "videos", onAdd: function (event) {
            console.log(event);
            var video = event.item.querySelector('video');
            video.play();
        }
    });
});

var Chat = {
    chatlog: null,
    init: function () {
        Chat.chatlog = document.getElementById("chat-feed");
    },
    snFromAccountID: function (id) {
        var len = Room.data.Accounts.length;
        for (var i = 0; i < len; i++) {
            if (Room.data.Accounts[i].ParticipantID == id) {
                return Room.data.Accounts[i].ScreenName;
            }
        }
    },
    sendMessage: function () {
        var tarea = document.getElementById("send-box").querySelector("input");
        var text = tarea.value;
        if (text != "")
            Room.sendMessage(text);
        tarea.focus();
        tarea.value = "";
    },
    uploadFile: function (files) {
        var file = files[0];
        if (file.size > 0 && file.size < 536870912) {
            document.getElementById("file-prog").style.display = "block";
            var form = new FormData();
            var xhr = new XMLHttpRequest();
            form.append("action", "upload");
            form.append("token", Account.data.LoginToken);
            form.append("upload", file);
            form.append("room", Room.data["RoomID"]);
            xhr.open("POST", "/assets/php/components/room.php");
            xhr.upload.onprogress = function (e) {
                $('#file-prog').progress({
                    percent: Math.ceil((e.loaded / e.total) * 100)
                });
            };
            xhr.send(form);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {

                        var response = JSON.parse(xhr.responseText);
                        console.log("response: ", xhr);
                        Room.uploadFile(response);
                    } else {
                        console.log("Error", xhr.statusText);
                    }
                    //insert line here
                        document.getElementById("file-prog").style.display = "none";
                        $('#file-prog').progress({
                            percent: 0
                        })
                }
            }
        } else {
            console.log("Filesize invalid");
            Toast.error(textNode("Must be under 256MB"));
        }
    },
    initDragDrop: function () {
        var doc = document;
        var mask = document.getElementById("upload-mask");
        var xhr = new XMLHttpRequest();

        if (xhr.upload) {
            doc.addEventListener("dragover", Chat.displayOverlay, false);
            mask.addEventListener("dragleave", function () {
                document.getElementById("upload-overlay").style.display = "none";
                document.getElementById("upload-mask").style.display = "none";
            }, false);
            mask.addEventListener("drop", Chat.fileSelectorHandler, false);
        }
    },
    displayOverlay: function (e) {
        document.getElementById("upload-mask").style.display = "block";
        document.getElementById("upload-overlay").style.display = "block";
        Chat.fileDragHover(e);
    },
    fileSelectorHandler: function (e) {
        document.getElementById("upload-overlay").style.display = "none";
        document.getElementById("upload-mask").style.display = "none";
        Chat.fileDragHover(e);
        Chat.uploadFile(e.dataTransfer.files);
    },
    fileDragHover: function (e) {
        e.stopPropagation();    //prevent file drag from effecting parent nodes
        e.preventDefault();     //prevent web browser from responding when file is dragged over using default settings
    },
    updateScroll: function () {
        var element = document.getElementById("right-hand-pane");
        element.scrollTop = element.scrollHeight;
    },
    putMessage: function (sender, _text, before, fileid) {
        var text;
        if (fileid)
            text = "<a id='file-" + fileid + "' class='hyperlink' href='javascript:Chat.RequestDownload(" + fileid + ")'>" + _text + "</a>";
        else
            text = Autolinker.link(_text);

        var messageLog = document.getElementById("chat-feed");
        var username = Room.data.Accounts[sender].ScreenName;
        var chat_messages = document.createElement("div");
        var author;

        if (sender === Account.data.ID) {
            author = "<p class='author user mine uid-" + sender + "'>";
            username += " (you)";
        }
        else
            author = "<p class='author user uid-" + sender + "'>";

        chat_messages.innerHTML = "<div class='ui fitted divider'></div><div class='content'>" + author + username + "</p><div class='text'><p>" + text + "</p></div></div>";
        if (before)
            messageLog.insertBefore(chat_messages, messageLog.firstChild);
        else
            messageLog.appendChild(chat_messages);

        Chat.updateScroll();
    },
    DownloadFile: function (fileurl, filename, fileid) {
        if (document.getElementById('fileprog-' + fileid) === null) {
            var xhr = new XMLHttpRequest();
            var file_selected = document.getElementById("file-" + fileid);
            var download_prog = document.createElement("div");
            download_prog.className = "ui tiny progress";
            download_prog.id = "fileprog-" + fileid;
            download_prog.innerHTML = "<div class='bar'> <div class='progress'></div> </div>";
            file_selected.appendChild(download_prog);

            xhr.open('GET', fileurl);
            xhr.responseType = "arraybuffer";
            xhr.onload = function () {
                var blob = new Blob([xhr.response], {type: "application/octet-stream"});
                saveAs(blob, filename.concat(".zip"));
            };

            xhr.onprogress = function (e) {
                $('#fileprog-' + fileid).progress({
                    percent: Math.ceil((e.loaded / e.total) * 100)
                });
            };
            xhr.onloadend = function (e) {
                setTimeout(function () {
                    document.getElementById('file-' + fileid).removeChild(download_prog);
                }, 1000);
            };
            xhr.send(null);
        }
    },
    RequestDownload: function (fileid) {
        Room.requestDownload(fileid);
    },
    repopulateMessages: function () {
        var before = false;
        for (var key in Messages) {
            if (Messages.hasOwnProperty(key)) {
                var sender = Messages[key].author;
                var text = Messages[key].content;
                var fileid = Messages[key].fileid;
                Chat.putMessage(sender, text, before, fileid);
                before = true;
            }
        }
    }
};

var Room = {
    data: null,
    socket: null,
    connected: false,
    connect: function () {
        if (!Room.data) return;
        var url = "wss:dev.slingapp.net/rooms/";
        if (window.location.host === "localhost") {
            url = "wss:localhost/rooms/";
        }
        url += Room.data.RoomID;

        // console.log("Attempting to connect to ", url);
        Room.socket = new WebSocket(url);

        Room.socket.onopen = function () {
            Room.socket.send(JSON.stringify({
                action: "Register",
                token: Account.data.LoginToken,
                room: Room.data.ID
            }));
            Room.connected = true;
        };
        Room.socket.onmessage = function (data) {
            var message = JSON.parse(data.data);
            // console.log(message);
            if (message.notify) {
                Toast.pop(message.notify, 3000);
            }
            var type = message.type;
            switch (type) {
                case "Message": {
                    var text = message.text;
                    var sender = message.sender;
                    var fileid = message.fileid;
                    Chat.putMessage(sender, text, null, fileid);

                }
                    break;

                case "Connect Video": {

                }
                    break;

                case "Disconnect Video": {

                }
                    break;

                case "Participant Joined": {
                    var accountID = message.id;
                    var sn = message.nick;
                    Room.data.Accounts[accountID] = {ScreenName: sn, ID: accountID};
                    AVC.getUserPreviewNode(accountID);
                    if(AVC.audioConnected){
                        AVC.getPeerAudioStream(accountID + "a");
                    }

                    AVC.getPeerVideoStream(accountID + "v", function (stream) {
                        var id = peerid;
                        // console.log('got peer video');
                        AVC.setPeerVideoNode(id, stream);
                    });
                }
                    break;

                case "Confirmation": {
                    if (message.success === false) {
                        Toast.error(textNode("Action: " + message.action + " failed"));
                    }
                }
                    break;

                case "Download": {
                    Chat.DownloadFile(message.filepath, message.filename, message.fileid);
                }
                    break;
                case "Room Code Changed": {
                    Room.data.RoomCodes[message.inviteCode].UsesRemaining = message.uses;
                    Room.updateInvites();
                }
                    break;

                case "Room Code Deleted": {
                    Room.data.RoomCodes[message.inviteCode] = null;
                    Room.updateInvites();
                    break;
                }

                case "Participant Changed Their Name": {
                    UserSection.updateUserInfo(message.id, message.nick);
                }
                    break;

                default: {
                    // console.info(message);
                }
            }
        };
        Room.socket.onerror = function (error) {
            Toast.error(textNode("Room Connection Error"));
            console.error(error);
        };
        setTimeout(function () {
            if (!Room.connected) {
                Toast.error(textNode("Could not connect to Room"));
            }
        }, 5000);
    },
    send: function (json) {
        if (Room.connected) {
            Room.socket.send(JSON.stringify(json));
        } else {
            Room.connect();
        }
    },
    sendMessage: function (message) {
        if (message.length <= 2000) {
            var json = {
                action: "Send Message",
                token: Account.data.LoginToken,
                text: message
            };

            Room.send(json);
        } else {
            alert("That message is too big!  Limit your messages to 2000 characters.");
        }
    },
    uploadFile: function (fileJSON) {
        fileJSON.action = "Send Message";
        fileJSON.token = Account.data.LoginToken;

        Room.send(fileJSON);
    },
    requestDownload: function (fileid) {
        // console.log("requesting to download");
        Room.socket.send(JSON.stringify({
            action: "Download File",
            token: Account.data.LoginToken,
            fileid: fileid
        }));
    },
    createInviteCode: function (e) {
        var roomid = Room.data.RoomID;
        $.ajax({
            type: 'post',
            url: '/assets/php/components/room.php',
            dataType: 'JSON',
            data: {
                action: "gencode",
                room: roomid,
                token: Account.data.LoginToken
            },
            success: function (data) {
                Room.data.RoomCodes[data.Code] = data;
                Room.updateInvites();
            },
            error: function (error) {
                // console.log(error);
            }
        });
    },
    getRoomCodes: function () {
        return Room.data.RoomCodes;
    },
    createRoomCode: function (uses, expires) {
        Room.socket.send(JSON.stringify({
            action: "Create Room Code",
            token: Account.data.LoginToken,
            uses: uses,
            expires: expires
        }));
    },
    snFromId: function (id) {
        return Room.data.Accounts[id].ScreenName;
    },
    settings: {
        categoryPanel: {
            links: null,
            node: null
        },
        optionsPanel: {
            panels: null,
            node: null
        }
    },
    updateInvites: function () {
        var invitepanel = Room.settings.optionsPanel.querySelector("#Invites");
        var iCodeDiv = invitepanel.querySelector("#invite-codes");
        iCodeDiv.innerHTML = "";
        for (var code in Room.data.RoomCodes) {
            if (Room.data.RoomCodes.hasOwnProperty(code) && Room.data.RoomCodes[code] != null) {
                var rc = Room.data.RoomCodes[code];
                var tr = document.createElement("tr");
                tr.object = rc;
                tr.innerHTML += "<td><input class='form-control iv-code' onclick='Room.select()' readonly value='" + rc.Code + "'></td>";
                tr.innerHTML += "<td>" + Room.data.Accounts[rc.Creator].ScreenName + "</td>";
                tr.innerHTML += "<td>" + (rc.UsesRemaining != null ? rc.UsesRemaining : "Unlimited") + "</td>";
                tr.innerHTML += "<td>" + (rc.Expires != null ? rc.Expires : "none") + "</td>";

                iCodeDiv.appendChild(tr);
                Settings.addCodeEvent(tr);
            }
        }
    },
    SetupSemanticUI: function () {
        if (RoomCookies.getCookie("theme") === "light")
            Settings.resetTheme();

        $('.ui.accordion')
            .accordion({
                exclusive: false
            });

        $('.tabular.menu .item').tab();

        $('.ui.left.sidebar').sidebar({
            dimPage: false,
            transition: 'overlay',
            closable: false,
        })
            .sidebar('attach events', '#menu')
            .sidebar("show");

        $('.ui.dropdown').dropdown();

        $("#settings-button")
            .popup({
                inline: true,
                hoverable: true,
                position: 'bottom left',
                delay: {
                    hide: 400
                },
                onHidden: Settings.quickMenuClose
            })
            .rotate({
                bind: {
                    mouseover: function () {
                        $(this).children("i").rotate({
                            angle: 0,
                            animateTo: 45
                        })
                    }
                }
            });

        $("#regen-code")
            .rotate({
                bind: {
                    mouseover: function () {
                        $(this).rotate({
                            angle: 0,
                            animateTo: 360
                        })
                    }
                }
            });

        $("#leave-button")
            .popup();

        $("#share-button")
            .popup();

        $('#plugin-prompt')
            .modal();

        $('#range-1').range({
            min: 0,
            max: 100,
            start: RoomCookies.getCookie('inVol'),
            onChange: function (value) {
                $('#display-1').html(value);
                RoomCookies.setCookie('inVol', value, 365);
            }
        });
        $('#range-2').range({
            min: 0,
            max: 100,
            start: RoomCookies.getCookie('outVol'),
            onChange: function (value) {
                $('#display-2').html(value);
                RoomCookies.setCookie('outVol', value, 365);
            }
        });
    },
    closePluginPrompt: function() {
        $('#plugin-prompt').modal('hide');
    },
    openExtensionTab: function() {
        window.open('https://chrome.google.com/webstore/detail/sling-desktop-share/pecpbndlndfegjibmbaplkjdkhdgmnei', '_blank');
        Room.closePluginPrompt();
    }
};

var UserSection = {
    updateUserInfo: function (accountID, nickname) {
        Room.data.Accounts[accountID].ScreenName = nickname;
        $('.uid-' + accountID).html(nickname);
    },
    newUserSet: function (size, target) {
        if (size == 'small') {   //Small + no EventTarget sent
            for (var key in Room.data.Accounts) {
                if (Room.data.Accounts.hasOwnProperty(key)) {
                    var account = Room.data.Accounts[key];
                    //Check to make sure that this user div does not already exist
                    if (document.getElementById('NU' + key.toString()) == null) {
                        // console.log("in New User Set");
                        var newUser = document.createElement('div');
                        newUser.id = 'NU' + key.toString();
                        newUser.className = 'roomSide';
                        document.getElementById('screensList').appendChild(newUser);

                        document.getElementById('NU' + key.toString()).setAttribute("onclick", "UserSection.expandDiv(event)");
                        document.getElementById('NU' + key.toString()).setAttribute("ondblclick", "UserSection.sendDivToCenter(event)");

                        var newUserTitle = document.createElement('div');
                        newUserTitle.id = 'UT' + key.toString();
                        newUserTitle.className = 'roomSideTitle';
                        document.getElementById('NU' + key.toString()).appendChild(newUserTitle);

                        var newUserName = document.createElement('span');
                        newUserName.id = 'UN' + key.toString();
                        newUserName.className = 'vertical-text';
                        document.getElementById('UT' + key.toString()).appendChild(newUserName);
                        document.getElementById('UN' + key.toString()).innerHTML = account.ScreenName;
                    }
                }
            }
        }
        else {   //Large + EventTarget sent
            for (var keyMS in Room.data.Accounts) {
                if (Room.data.Accounts.hasOwnProperty(keyMS)) {
                    if (target.id == 'NU' + keyMS.toString()) {
                        //This is the target Screen we want to make a large version of

                        var accountMS = Room.data.Accounts[keyMS];

                        // console.log("in New User Set");
                        var newUserMS = document.createElement('div');
                        newUserMS.id = 'NU' + keyMS.toString() + 'mainScreen';
                        newUserMS.className = 'screen';
                        document.getElementById('ScreenContainer').appendChild(newUserMS);

                        var newUserTitleMS = document.createElement('div');
                        newUserTitleMS.id = 'UT' + keyMS.toString() + 'mainScreen';
                        newUserTitleMS.className = 'roomSideTitleMS';
                        document.getElementById('NU' + keyMS.toString() + 'mainScreen').appendChild(newUserTitleMS);

                        var newUserNameMS = document.createElement('span');
                        newUserNameMS.id = 'UN' + keyMS.toString() + 'mainScreen';
                        newUserNameMS.className = 'vertical-text';
                        document.getElementById('UT' + keyMS.toString() + 'mainScreen').appendChild(newUserNameMS);
                        document.getElementById('UN' + keyMS.toString() + 'mainScreen').innerHTML = accountMS.ScreenName;
                        document.getElementById('NU' + keyMS.toString() + 'mainScreen').setAttribute("onclick", "UserSection.returnDivToSide(event)");
                    }
                }
            }
        }
    },
    //These all only Remain until page reload, they are wiped then.
    expandDiv: function (event) {
        var target = event.target;
        if (event.target.id[0] == 'N') {
            if (target != null) {
                // console.log(event.target.id);
                target.className = 'eRoomSide';
                target.setAttribute("onclick", "UserSection.minimizeDiv(event)");
            }
        }
    },
    minimizeDiv: function (event) {
        var target = event.target;
        if (event.target.id[0] == 'N') {
            if (target != null) {
                target.className = 'roomSide';
                target.setAttribute("onclick", "UserSection.expandDiv(event)");
            }
        }
    },
    sendDivToCenter: function (event) {
        var target = event.target;
        if (target != null && document.getElementById(target.id.toString() + 'mainScreen') == null) {
            UserSection.newUserSet('large', target);
        }
    },
    returnDivToSide: function (event) {
        var target = event.target;
        if (target != null) {
            var item = document.getElementById(target.id);
            item.parentNode.removeChild(item);
        }
    }
};

var Settings = {
    themed_elems: document.getElementsByClassName("theme1"),
    colored_elems: document.getElementsByClassName("theme2"),
    openSettings: function (tab) {
        $('.ui.modal')
            .modal('show')
        ;
        $('.modal a.' + tab)
            .click()
        ;
    },
    quickScreenNameChange: function () {
        document.getElementsByClassName("quickbutton")[0].style.display = "none";
        document.getElementById("quick-input").style.display = "inline";
        document.getElementById("quick-name-change").focus();
    },
    quickMenuClose: function () {
        document.getElementsByClassName("quickbutton")[0].style.display = "block";
        document.getElementsByClassName("quickbutton")[1].style.display = "block";
        document.getElementById("quick-input").style.display = "none";
        document.getElementById("quick-invite").style.display = "none";
    },
    closeSettings: function () {
        $('.ui.modal')
            .modal('hide')
        ;
    },
    changeScreenName: function (name) {
        if (name.length > 0 && name[0] !== " ") {
            var json = {
                action: "Change Name",
                user: name,
                token: Account.data.LoginToken
            };
            Room.socket.send(JSON.stringify(json));
        }
    },
    quickInvite: function (regenerate) {
        var quick_inv = document.getElementById("quick-invite-textbox");
        var invite_button = document.getElementById("quick-invite-button");

        invite_button.style.display = "none";
        document.getElementById("quick-invite").style.display = "inline";

        $(quick_inv)
            .popup({
                content: ""
            })
            .popup("hide");

        if(regenerate)
            quick_inv.value = "generating...";

        if (!quick_inv.value.localeCompare("generating...")) {
            var roomid = Room.data.RoomID;
            $.ajax({
                type: 'post',
                url: '/assets/php/components/room.php',
                dataType: 'JSON',
                data: {
                    action: "gencode",
                    room: roomid,
                    token: Account.data.LoginToken
                },


                success: function (data) {

                    quick_inv.value = data.Code;
                    quick_inv.select();
                    $(quick_inv)
                        .popup({
                            content: "Select to Copy"
                        })
                        .popup("show");
                },
                error: function (error) {
                    // console.log(error);
                }
            });
        }
    },
    copyCode: function () {
        try {
            var successful = document.execCommand('copy');
            var quick_inv =  document.getElementById("quick-invite-textbox");
            if(successful) {
                // $(quick_inv).popup('hide');
                // Room.SetupSemanticUI();

                $(quick_inv)
                    .popup({
                        content : "Copied to clipboard"
                    })
                    .popup('show');
            }
            else {
                console.log("failed to copy");
                $(quick_inv).popup('hide');
            }
        } catch (err) {
            console.log("execCommand not supported");
        }
    },
    changeRemainingUses: function (code) {
        var uses = prompt("Enter remaining uses:");
        event.preventDefault();
        event.stopPropagation();
        var json = {
            action: "Change Uses",
            remaining: uses,
            token: Account.data.LoginToken,
            invite: code
        };
        Room.socket.send(JSON.stringify(json));

        Room.updateInvites();
        return false;
    },
    changeExpirationDate: function (code) {
        var expires = prompt("Enter expiration date:", "mm/dd/yyyy");
        event.preventDefault();
        event.stopPropagation();
        var json = {
            action: "Change Expiration Date",
            expiration: expires,
            token: Account.data.LoginToken,
            invite: code
        };
        Room.socket.send(JSON.stringify(json));
        Room.updateInvites();

        return false;
    },
    deleteInviteCode: function (code) {
        event.preventDefault();
        event.stopPropagation();
        var uses = 99;
        var json = {
            action: "Delete Code",
            token: Account.data.LoginToken,
            remaining: uses,
            invite: code
        };
        Room.socket.send(JSON.stringify(json));
        Room.updateInvites();

        return false;
    },
    getCodeNodeList: function (code) {
        //var label = ContextMenu.createLabel(code + " Options");

        //console.log("Menu Link");
        var setUses = ContextMenu.createMenuLink("Set Uses", "", function () {
            Settings.changeRemainingUses(code);
            ContextMenu.close();
        });

        var expiration = ContextMenu.createMenuLink("Set Expiration Date", "", function () {
            Settings.changeExpirationDate(code);
            ContextMenu.close();
        });

        var deleteCode = ContextMenu.createMenuLink("Delete", "", function () {
            Settings.deleteInviteCode(code);
            ContextMenu.close();
        });

        return [setUses, expiration, deleteCode];
    },
    addCodeEvent: function (element) {
        element.addEventListener("contextmenu", function (event) {
            // console.log(n.object.Code);
            //console.log(n);
            ContextMenu.create(event, Settings.getCodeNodeList(element.object.Code));
            return false;
        });
    },
    addCodeButtonEvents: function () {
        var codes = Room.settings.optionsPanel.querySelector("#Invites").querySelectorAll("tr");

        // console.log(close);

        codes.forEach(function (n) {
            //console.log("Button Test");

            n.addEventListener("contextmenu", function (event) {
                // console.log(n.object.Code);
                //console.log(n);
                ContextMenu.create(event, Settings.getCodeNodeList(n.object.Code));
                return false;
            });
        });
    },
    appendInviteCode: function (code) {
        var table = document.getElementById('invite-code-table');
        var row = document.createElement('tr');
        row.innerHTML = "<td>" + code.Code + "</td><td>" + Room.snFromId(code.Creator) + "</td><td>" + +"</td><td>" + +"</td>";
        table.appendChild(row);
    },
    createInvite: function () {
        API.room.createInvite(Room.data.RoomID, function (data) {
            console.log(data);
            Settings.appendInviteCode(data.code);
        });
    },
    toggleTheme: function (elem) {
        var themeChoice = RoomCookies.getCookie("theme");

        if (themeChoice === "dark") {
            RoomCookies.setCookie("theme", "light");
            Settings.resetTheme();
            Settings.swapStyleSheet("room_light.css");
        } else {
            RoomCookies.setCookie("theme", "dark");

            [].forEach.call(this.themed_elems, function (e) {
                e.classList.add("inverted");
            });
            [].forEach.call(this.colored_elems, function (e) {
                e.classList.add("black");
            });
            Settings.swapStyleSheet("room_dark.css");
            elem.innerHTML = "Light Theme"
        }
    },
    resetTheme: function () {
        [].forEach.call(this.themed_elems, function (e) {
            e.classList.remove("inverted");
        });
        [].forEach.call(this.colored_elems, function (e) {
            e.classList.remove("black");
        });
        document.getElementById("quick-theme-button").innerHTML = "Dark Theme"
    },
    swapStyleSheet: function (sheet) {
        document.getElementById("pagestyle").setAttribute("href", "/assets/css/" + sheet);
    }
};

//////////////////////////////////
// Cookie Room Instance Values  //
//////////////////////////////////

var RoomCookies = {
    setCookie: function (cookieName, cookieValue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cookieName + "=" + cookieValue + ";" + expires + ";path=/";
    },
    getCookie: function (cookieName) {
        var name = cookieName + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    },
    checkOutVol: function () {
        var outVol = RoomCookies.getCookie("outVol");
        if (outVol != "") {
            //On Reload Get This value
        } else {
            RoomCookies.setCookie("outVol", 100, 365);
            if (outVol != "" && outVol != null) {
                RoomCookies.setCookie("outVol", 100, 365);
            }
        }
    },
    checkInVol: function () {
        var inVol = RoomCookies.getCookie("inVol");
        if (inVol != "") {
            //On Reload Get This value
        } else {
            RoomCookies.setCookie("inVol", 100, 365);
            if (inVol != "" && inVol != null) {
                RoomCookies.setCookie("inVol", 100, 365);
            }
        }
    }
};

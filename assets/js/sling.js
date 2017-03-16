/**
 * Created by ian on 11/12/16.
 */
console.info("Loaded sling.js");
var Resource = {
    info:{},
    dictionary:{},
    /**
     *
     * @param resource - the web path of the resource to be loaded
     * @param name - the name to reference the resource by
     * @param callback - an optional function to call when the resource has been loaded
     */
    load:function (resource, name, callback) {
        Resource.info[name] = {resource:resource, status:"Not Loaded"};
        get(resource, "", function(data){
            Resource.dictionary[name] = document.createElement("div");
            Resource.dictionary[name].innerHTML = data;
            if(callback) callback(data);
            Resource.info[name].status = "Loaded";
        });

    },
    require:function(name){
          
    },
    get:function(name){
        return Resource.dictionary[name];
    }
};

var Toast = {
    pop:function(node, classname, timeout){
        if(!timeout) timeout = 3000;
        var toast = document.createElement("div");
        toast.className = "toast " + classname;
        toast.appendChild(node);
        document.body.appendChild(toast);
        setTimeout(function(){
            document.body.removeChild(toast);
        },timeout)
    },
    error:function (node, timeout) {
        Toast.pop(node, "error", timeout);
    }
};

var Modal = {
    current:null,
    stack:[],
    modal:null,
    dict:{},
    init:function(){
        document.body.innerHTML += "<div id='modal' ></div>";
        Modal.modal = document.getElementById("modal");
    },
    create:function(resourceName, classname, onblur) {
        var modalContents = Resource.get(resourceName);
        if (modalContents != null) {
            modalContents.className = "flex";
            Modal.modal.innerHTML = "";
            Modal.modal.appendChild(modalContents);
            Modal.modal.className = classname;
            Modal.modal.style.visibility = "visible";

            Modal.modal.onclick = function () {
                Modal.hide();
                if (onblur) {
                    console.log("onblur");
                    onblur();
                }
            };
            Modal.current = resourceName;
        } else {
            console.error("Resource " + resourceName + " has not been loaded.  Load the resource first with Resource.load()");
        }
    },
    hide:function(){
        if(Modal.current){
            Modal.modal.style.visibility = "hidden";
        }
    },
    show:function(resource){
        if(resource){
            if(resource in Modal.dict) return false;
            Modal.modal.innerHTML = "";
            Modal.modal.appendChild(Modal.dict[resource]);
        }else{
            Modal.modal.style.visibility = "visible";
        }
        return true;
    },
    destroy:function(){
        if(Modal.current){
            Modal.current = null;
        }
    },
    pushCurrent:function(){
        if(Modal.current){
            var current = Modal.modal.firstChild;
            Modal.stack.push(current);
        }
    }
};


var ContextMenu = {
    current:null,
    base:null,
    init:function(){
        ContextMenu.base = document.createElement("div");
        ContextMenu.base.className = "context-menu";
        ContextMenu.base.onclick = function (event) {
            event.stopPropagation();
        };

        document.addEventListener("click", function(){
            if(ContextMenu.current){
                document.body.removeChild(ContextMenu.current);
                ContextMenu.current = null;
            }
        });
    },
    /**
     *
     * @param mouseEvent
     * @param nodeList
     * @returns {boolean}
     *
     * Decription:  Creates a context menu and appends it to the body of the HTML doc.
     *              Requires the right-click mouse event and a list of HTML Elements
     *              that are to be included inb the context menu
     */
    create:function(mouseEvent, nodeList){
        mouseEvent.preventDefault();
        mouseEvent.stopPropagation();
        ContextMenu.close();

        if(!ContextMenu.base) ContextMenu.init();

        ContextMenu.current = ContextMenu.base.cloneNode(false);
        ContextMenu.appendNodes(nodeList);
        ContextMenu.current.style.left = event.pageX;
        ContextMenu.current.style.top = event.pageY;
        document.body.appendChild(ContextMenu.current);
        return false;
    },
    /**
     * Closes the current open context menu
     */
    close:function(){
        if(ContextMenu.current){
            document.body.removeChild(ContextMenu.current);
        }
    },
    /**
     * Do not call publicly, only internally used
     * @param nodeList
     */
    appendNodes:function(nodeList){
        var menuitemslen = nodeList.length;
        for(var i = 0; i<menuitemslen; i++){
            ContextMenu.current.appendChild(nodeList[i]);
        }
    },
    /**
     *
     * @param label
     * @param classname
     * @param onclick
     * @returns {Element}
     * Description: Creates a menu link (<a> element).  Requires the name of the link, any
     *              classnames the link should have, can be none, and the callback to execute
     *              when the link is clicked.
     */
    createMenuLink:function(label, classname, onclick){
        var link = document.createElement("a");
        link.innerHTML = label;
        link.className = classname;
        link.onclick = onclick;
        return link;
    },
    createLabel:function(text){
        var label = document.createElement('p');
        label.innerHTML = text;
        return label;
    }
};

function get(url, parameters, callback){
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if(xhr.readyState == 4){
            callback(xhr.responseText, xhr.status);
        }
    };
    xhr.open("GET", url + parameters, true);
    xhr.send();
}

/******************************************************************************************************************
                                                // ACCOUNT FUNCTIONS //
******************************************************************************************************************/
function submitLogin() {
    var form = document.getElementById("loginForm");
    var email = form.elements["email"].value;
    var password = form.elements["pass1"].value;
    document.getElementById("loginerror").innerHTML = "<div class='sling' style=''></div>";
    return $.ajax({
        type: 'post',
        url: 'assets/php/components/account.php',
        dataType: 'JSON',
        data: {
            action: "login",
            email: email,
            pass1: password
        },
        success: function (data) {
            if (validateCredentials(data)) {
                var button = document.getElementById("login-button");
                button.innerHTML = "Logout";
                button.className = "login-button";
                document.getElementById("loginForm").reset();
                SetCookie("Token", data.LoginToken, 7);
                Modal.hide();
                getRoomData();
            }
            //Provide Recent Rooms Info

            return data;
        },
        error: function (error) {
            console.log(error);
        }
    });
}

function showLogin() {
    document.getElementById("login-button").className += " open";

    setTimeout(function () {
            Modal.create("Login Form", "", hideLogin)
        }, 700
    );
}

function isLoggedIn() {

    var token;

    if(!(token = GetToken()))
        tempRegister();
    else
        CheckTokenValidity(token, 'tempRegister');

    var login = document.getElementById("login-button");
    var screenshot = document.getElementById("screenshot");
    var loggedIn = (token && token[0] == '1');
    login.innerHTML = (loggedIn) ? "Logout" : "Login<span id='reg'><br>or sign up</span>";
    //Provide Recent Rooms Info
    return loggedIn;
}

function hideLogin(data) {
    var button = document.getElementById("login-button").className = "login-button";
    Modal.hide();
}

function submitRegister() {
    var form = document.getElementById("registerForm");
    var first = form.elements["fname"].value;
    var last = form.elements["lname"].value;
    var email = form.elements["email"].value;
    var pass1 = form.elements["pass1"].value;
    var pass2 = form.elements["pass2"].value;
    var token = GetToken();

    // console.log(token);

    var error = document.getElementById("registererror");
        error.innerHTML = "<div class='sling' style=''></div>";
    return $.ajax({
        type: 'post',
        url: 'assets/php/components/account.php',
        dataType: 'JSON',
        data: {
            action: "register",
            fname: first,
            lname: last,
            email: email,
            pass1: pass1,
            pass2: pass2,
            token: token
        },
        success: function (data) {
            var button = document.getElementById("login-button");
            button.innerHTML = "Logout";
            button.className = "login-button";
            console.log(data);
            SetCookie("Token", data.LoginToken, 7);
            error.innerHTML = "";
            document.getElementById("registerForm").reset();
            Modal.hide();
            return data;
        },
        error: function (error) {
            console.log(error);
        }
    });
}

function tempRegister() {
    return $.ajax({
        type: 'post',
        url: 'assets/php/components/account.php',
        dataType: 'JSON',
        data: {
            action: "nocookie"
        },
        success: function (data) {
            SetCookie("Token", data.LoginToken, 7);
            Modal.hide();
            return data;
        },
        error: function (error) {
            console.log(error);
        }
    });
}

function logout() {
    DeleteCookie("Token");
    document.getElementById("login-button").innerHTML = "Login<span id='reg'><br>or sign up</span>";
}

function validateCredentials(data) {
    var loginError = document.getElementById("loginerror");

    if (data) {
        loginError.innerHTML = "<br>";
        return true;
    }
    else {
        loginError.innerHTML = "Username or password is Incorrect";
        return false;
    }
}

function getRoomData() {
    return $.ajax({
        type: 'post',
        url: 'assets/php/components/account.php',
        dataType: 'JSON',
        data: {
            action: "roomdata",
            token: GetToken()
        },
        success: function (data) {
            console.log(data);
            for(elem = 0; elem < 10; elem++){
                if(data.length > 0) {
                    var roomName = data[elem].RoomName;
                    var active = data[elem].Active;
                    if (roomName.length == 0)
                        roomName = " Unnamed ";
                    if(active == 1)
                        active = " ACTIVE";
                    if(active == 0)
                        active = " INACTIVE";

                    var prevRoomName = document.createElement('div');
                    var dataStream = document.createElement('span');
                    dataStream.innerHTML = "RoomTitle: " + roomName + "\nStatus: " + active;
                    prevRoomName.id = "Room" + elem;
                    prevRoomName.className = 'sling-prev-room';
                    document.getElementById('RecentRooms').appendChild(prevRoomName);
                    // document.getElementById("Room" + elem)
                    prevRoomName.appendChild(dataStream);
                }
            }

            //Provide Recent Rooms Info
            return data;
        },
        error: function (error) {
            console.log(error);
        }
    });
}

/******************************************************************************************************************
                                              // ROOM FUNCTIONS //
 ******************************************************************************************************************/
function joinroom(event, f) {
    event.preventDefault();
    console.log(f);
    var code = f["room"].value;
    $.ajax({
        type: 'post',
        url: 'assets/php/components/room.php',
        dataType: 'JSON',
        data: {
            action: "join",
            token: GetToken(),
            code: code
        },
        success: function (data) {
            var roomid = data.RoomID;
            console.log(data);
            window.location = "/rooms/" + roomid;
        },
        error: function (error) {
            console.log(error);
        }
    });

    return false;
}

function CreateRoom(event, element) {
    var roomname = element.roomname.value;
    var token;

    if (!(token = GetToken()))
        tempRegister();

    var errordiv = element.querySelector("#error");

    errordiv.innerHTML = "<div class='sling' style=''></div>";
    $.ajax({
        type: 'post',
        url: 'assets/php/components/room.php',
        dataType: 'JSON',
        data: {
            action: "create",
            roomname: roomname,
            token: token
        },
        success: function (data) {
            errordiv.innerHTML = "Success";
            window.location = "/rooms/" + data.RoomID;
        },
        error: function (error) {
            console.log(error);
        }
    });
    event.stopPropagation();
    event.preventDefault();
}


/******************************************************************************************************************
                                            // COOKIE FUNCTIONS //
 ******************************************************************************************************************/

function GetToken() {
    var cstring = document.cookie;
    var cookies = cstring.split(";");
    var tokenstr = null;
    var rvalue;
    cookies.forEach(function (c) {
        if (c.search(/Token/) != -1)
            tokenstr = c;
    });
    if (tokenstr != null) {
        var keynval = tokenstr.split("=");
        // var key = keynval[0];
        rvalue = keynval[1];
    }
    else
        rvalue = null;
    return rvalue;
}

function SetCookie(key, value, daysTillExp) {
    var date = new Date();
    date.setTime(date.getTime() + (daysTillExp*24*60*60*1000));
    var expires = "expires="+ date.toUTCString();
    document.cookie = key + "=" + value + ";" + expires + ";path=/";
}

function DeleteCookie(key) {
    document.cookie = key +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

function CheckTokenValidity(token, callback){
    $.ajax({
        type: 'post',
        url: '/assets/php/components/account.php',
        dataType: 'JSON',
        data: {
            action: "tokenisvalid",
            token: token
        },
        error: function (error) {
            console.log(error);
        }
    });
}

/******************************************************************************************************************
                                            // MISC FUNCTIONS //
 ******************************************************************************************************************/

function toggleform(e) {
    if (e.value === "Join Room") {
        e.value = "";
        e.style.color = "black";
        e.style.backgroundColor = "#fefefe";
    }
    else if (e.value === "") {
        e.value = "Join Room";
        e.style.color = "white";
        e.style.backgroundColor = "#333333";
    } else {
    }
}

function noprop(e) {
    e.stopPropagation();
    return false;
}

HTMLElement.prototype.removeClass = function(classname) {
    this.className = this.className.replace(new RegExp(" ?" + classname), "");
};
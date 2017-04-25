/**
 * Created by Ian Murphy on 4/9/2017.
 */

var Toast = {
    pop:function(text, classname, timeout){
        if(!timeout) timeout = 3000;
        var toast = document.createElement("div");
        toast.className = "toast " + classname;
        toast.innerHTML = text;
        document.body.appendChild(toast);
        setTimeout(function(){
            document.body.removeChild(toast);
        },timeout)
    },
    error:function (text, timeout) {
        Toast.pop(text, "error", timeout);
    }
};

var Room = {
    showCreateRoomDialog:function () {
        Dialog.dialog({
            title:"Create a Room",
            content:"Before your room is ready, you must name it.",
            placeholder:"Room Name",
            button:{
                cancel:"Cancel",
                submit:"Create"
            }
        }, function (name) {
            API.room.new(name, function (result) {
                window.location.replace("/rooms/" + result.room.RoomID);
            })
        })
    },showJoinRoomDialog:function () {
        Dialog.dialog({
            title:"Join a Room",
            content:"Enter the invite code for the Room you want to join.",
            placeholder:"Invite Code",
            button:{
                cancel:"Cancel",
                submit:"Join"
            }
        }, function (name) {
            document.body.classList.add("loading");
            API.room.join(name, function (result) {
                console.log(result);
                window.location.replace("/rooms/" + result.room.RoomID);
            },function (error) {
                console.error(error);
                var err = error.responseJSON.error;
                Toast.error(err);
            })
        })
    }
};

var Account = {
    changeName:function (newName) {
        API.me({Name:newName}, function (data) {
            window.location.reload();
        });
    },
    authenticate:function (email, password, onSuccess, onError) {
        API.user.authenticate(email, password, onSuccess, onError);
    },
    logout:function () {
        Cookie.delete("Token");
        window.location.reload();
    },
    showChangeNameDialog:function () {
        Dialog.dialog({
            title:"Change Name",
            content:"Change how you appear inside a Room",
            placeholder:"Screen Name",
            button:{
                cancel:"Cancel",
                submit:"Change"
            }
        }, function (name) {
            Account.changeName(name);
        })
    }
};

var Dialog = {
    box:$('#dialog-box'),
    title:$('#dialog-box').find(".dialog-title")[0],
    content:$('#dialog-box').find(".dialog-content")[0],
    buttonCancel:$('#dialog-box').find(".deny")[0],
    buttonSubmit:$('#dialog-box').find(".approve")[0],
    placeholder:$('#dialog-box').find("input")[0],
    dialog:function (settings, onusersubmit) {
        Dialog.title.innerHTML = settings.title;
        Dialog.content.innerHTML = settings.content;
        Dialog.buttonCancel.innerHTML = settings.button.cancel;
        Dialog.buttonSubmit.innerHTML = settings.button.submit;
        Dialog.placeholder.setAttribute("placeholder", settings.placeholder);


        $('#dialog-box').modal({
            onApprove:function () {
                var value = $('#dialog-input')[0].value;
                onusersubmit(value);
            },
            approve:'.approve'
        }).modal('show');
    }
};

var API = {
    me:function (postData, onSuccess) {
        if(typeof postData == "object"){
            $.post("/api/me/", postData, onSuccess);
        }else{
            console.error("API.me requires the second argument be an object")
        }
    },
    user:{
        new:function(onSuccess){
            $.get("/api/user/new/", "", onSuccess);
        },
        authenticate:function (email, password, onSuccess, onError) {
            $.ajax({
                method: 'post',
                url: '/api/user/authenticate/',
                data: {
                    Email:email,
                    Password:password
                },
                success: onSuccess,
                error: onError
            });
        },
        register:function (email, firstName, lastName, password, onSuccess, onError) {
            $.ajax({
                method: 'post',
                url: '/api/user/register/',
                data: {
                    Email     : email,
                    FirstName : firstName,
                    LastName  : lastName,
                    Password  : password
                },
                success: onSuccess,
                error: onError
            });
        }
    },
    room:{
        new:function(roomName, onSuccess, onError){
            $.ajax({
                method: 'get',
                url: '/api/room/new/'+roomName,
                success: onSuccess,
                error: onError
            });
        },
        join:function (code, onSuccess, onError) {
            $.ajax({
                method: 'get',
                url: '/api/room/join/'+code,
                success: onSuccess,
                error: onError
            });
        },
        delete:function (roomid, onSuccess, onError) {
            $.get("/api/room/" + roomid + "/delete/", onSuccess);
            $.ajax({
                method: 'get',
                url: '/api/room/'+roomid+'/delete',
                success: onSuccess,
                error: onError
            });
        },
        createInvite:function(roomid, onSuccess, onError){
            $.ajax({
                method: 'get',
                url: "/api/room/" + roomid + "/invite/",
                success: onSuccess,
                error: onError
            });
        }
    }
};

function validation(regex, message) {
    return {pattern:regex, message:message};
}
var Regex = {
    password : validation(new RegExp(".{6,30}"), "Password Length: 6-30"),
    name     : validation(new RegExp("[a-zA-Z]{2,30}"), "Name Length: 2-30"),
    email    : validation(new RegExp("[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$"), "Invalid email address")
};

var Form = {
    validate:function (element, validation) {
        var field = element.parentNode;
        field.classList.remove('error');
        if(!validation.pattern.test(element.value)){
            field.classList.add('error');
            $(element).attr("data-content", validation.message);
            $(element).popup('show');
            return false;
        }else{
            $(element).popup('hide');
            return true;
        }
    },
    areEqual:function (inputA, inputB) {
        if(inputA.value !== inputB.value){
            inputA.parentNode.classList.add('error');
            inputB.parentNode.classList.add('error');
            $(inputA).attr("data-content", "Passwords must match");
            $(inputB).attr("data-content", "Passwords must match");
            $(inputA).popup('show');
            $(inputB).popup('show');
            return false;
        }else{
            $(inputA).popup('hide');
            $(inputB).popup('hide');
            inputA.parentNode.classList.remove('error');
            inputB.parentNode.classList.remove('error');
            return true;
        }
    }
};

var Cookie = {
    get:function (key) {
        var cookies = document.cookie.split(";").map(function (s) {
            return s.trim().split("=");
        });
        for(var i = 0; i<cookies.length; i++){
            if(cookies[i][0] === key) return cookies[i][1];
        }
        return null;
    },
    set:function (key, value, daysTilExpire, path) {
        if(!path) path = '/';
        if(!daysTilExpire) daysTilExpire = 1;
        var date = new Date();
        date.setTime(date.getTime() + (daysTilExpire*24*60*60*1000));
        var expires = "expires="+ date.toUTCString();
        document.cookie = key + "=" + value + ";" + expires + ";path="+path;
    },
    delete:function (key) {
        document.cookie = key +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    },
    dict:function () {
        var cookies = document.cookie.split(";").map(function (s) {
            return s.trim().split("=");
        });
        var dictionary = {};
        cookies.forEach(function (cookie) {
            try{
                dictionary[cookie[0]] = JSON.parse(cookie[1]);
            }catch (e){
                dictionary[cookie[0]] = cookie[1];
            }

        });
        return dictionary;
    }
};

function SubmitRegisterForm() {
    var form  = $('#registerForm')[0];
    var error = $('#registererror')[0];
    var first = form["fname"];
    var last  = form["lname"];
    var email = form["email"];
    var pass1 = form["pass1"];
    var pass2 = form["pass2"];

    if(!Form.validate(first, Regex.name)) return;
    if(!Form.validate(last, Regex.name)) return;
    if(!Form.validate(email, Regex.email)) return;
    if(!Form.validate(pass1, Regex.password)) return;

    if(!Form.areEqual(pass1, pass2)) return;

    form.classList.add('loading');

    API.user.register(email.value, first.value, last.value, pass1.value, function (data) {
        console.log(data);
        window.location.reload();
    }, function (errorMsg) {
        console.log(errorMsg.responseJSON);
        error.innerHTML = errorMsg.responseJSON.error;
        form.classList.remove('loading');
        form.classList.add('error');
    });
}

function SubmitLoginForm() {
    var form  = $('#loginForm')[0];
    var error = $('#loginerror')[0];
    var email = form["email"];
    var password = form["pass1"];

    error.innerHTML = "";

    if(!Form.validate(email, Regex.email)) return;
    if(!Form.validate(password, Regex.password)) return;

    form.classList.add("loading");
    Account.authenticate(email.value, password.value, function (data) {
        window.location.reload();
    }, function (errorMsg) {
        console.log(errorMsg);
        error.innerHTML = errorMsg.responseJSON.error;
        form.classList.add("error");
        form.classList.remove("loading");
    });
}

var Feed = {
    entries:[],
    container:$('.rss-feed')[0],
    init:function () {
        const numEntries = 20;
        $.get("/api/feed/" + numEntries, function (data) {
            Feed.entries = data;
            Feed.run();
        })
    },
    appendEntry:function (entryNumber) {
        var div = document.createElement("div");
        if(entryNumber > Feed.entries.length -1) entryNumber = 0;
        var entry = Feed.entries[entryNumber];
        var date = new Date(entry.updated);
        div.innerHTML += entry.author.name+" &middot; <a href='"+ entry.link['@attributes'].href +"' target='_blank'>" + entry.title + "</a> &middot; <span>" + date.toDateString() + "</span>";
        div.addEventListener("animationend", function () {
            Feed.container.removeChild(div);
            Feed.appendEntry(entryNumber + 1);
        });
        Feed.container.appendChild(div);
    },
    run:function () {
        var div = document.createElement("div");
        div.className = 'wrap';

        Feed.entries.forEach(function (entry) {
            // console.log(entry);
            var date = new Date(entry.updated);
            div.innerHTML += "" +
                "<div class='entry' onclick='location=\""+ entry.link['@attributes'].href +"\"'>" +
                "<span class='author'>" + entry.author.name.toUpperCase() +"</span> " +
                "<span class='date'>ON " + date.toLocaleDateString().toUpperCase() + ": </span>" +
                "<span class='comment'>\"" + entry.title.toUpperCase().trim() + "\"</span></div>" +
                "<span class='dot'> &middot; </span>";
        });
        Feed.container.appendChild(div);
    }

};

var Metrics = {
    metrics:{},
    container:$('#metrics')[0],
    init:function () {
        $.get("/api/metrics", function (data) {
            console.log(data);
            for(var m of data){
                Metrics.metrics[m.Event] = parseFloat(m.AvgTime);
            }
            Metrics.setPropertiesConditional('API_AUTHENTICATE', "Authentication", 90, 120);
            Metrics.setPropertiesConditional('WSS_SEND_MESSAGE', "WSS Publish Message", 5, 15);
            Metrics.setPropertiesConditional('WSS_REGISTER', "WSS Subscribe", 5, 15);
            Metrics.setPropertiesConditional('API_CREATE_BLANK_ACCOUNT', "Account Creation", 40, 80);
            Metrics.setPropertiesConditional('API_CREATE_ROOM_AND_JOIN_ACCOUNT', "Room Creation", 40, 80);
            // Metrics.setPropertiesConditional('API_CREATE_ROOM_AND_JOIN_ACCOUNT', "Room Creation", 40, 80);


        })
    },
    setPropertiesConditional:function (key, eventLabel, good, intermediate) {
        if(!key in Metrics.metrics) return;
        var time = Metrics.metrics[key];
        var div = document.createElement("div");
        div.className = "ui button tiny inverted basic metric";
        div.innerHTML = eventLabel;
        div.setAttribute("data-tooltip", time + "ms average process time");
        div.setAttribute("data-inverted", "");
        if(time < good){
            div.classList.add("green");
            // div.innerHTML += ": Good";
        }
        else if (time < intermediate){
            div.classList.add("orange");
            // div.innerHTML += ": Alright";
        }
        else{
            div.classList.add("red");
            // div.innerHTML = ": Bad";
        }
        Metrics.container.appendChild(div);
    }
};
/**
 * Created by ian on 11/12/16.
 */
console.info("Loaded sling.js");
var Resource = {
    dictionary:{},
    /**
     *
     * @param resource - the web path of the resource to be loaded
     * @param name - the name to reference the resource by
     * @param callback - an optional function to call when the resource has been loaded
     */
    load:function (resource, name, callback) {
        get(resource, "", function(data){
            Resource.dictionary[name] = document.createElement("div");
            Resource.dictionary[name].innerHTML = data;
            if(callback) callback(data);
            console.info("Finished loading " + name);
        });

    },
    get:function(name){
        return Resource.dictionary[name];
    }
};

var Toast = {
    pop:function(node, timeout){
        var toast = document.createElement("div");
        toast.className = "toast";
        toast.appendChild(node);
        document.body.appendChild(toast);
        setTimeout(function(){
            document.body.removeChild(toast);
        },timeout)
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
                if (onblur) onblur();
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

function noprop(event){
    event.stopPropagation();
}

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
        var key = keynval[0];
        rvalue = keynval[1];
        // console.log(keynval);
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

function AssureCookie(){
    var token = GetToken();

    CheckTokenValidity(token, function (valid) {
        if(!valid){
            $.ajax({
                type: 'post',
                url: '/assets/php/components/account.php',
                dataType: 'JSON',
                data: {
                    action: "nocookie"
                },
                success: function (data) {
                    SetCookie("Token", data.LoginToken, 7);
                },
                error: function (error) {
                    console.log(error);
                }
            });
        }
    });
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
        success: function (token) {
            if(callback) callback(token.valid);
        },
        error: function (error) {
            console.log(error);
        }
    });
}

var Account = {
    data:null,
    login:function(){
        var token = GetToken();
        console.log(token);
        $.ajax({
            type: 'post',
            url: '/assets/php/components/account2.php',
            dataType: 'JSON',
            data: {
                action:"login",
                token:token
            },
            success: function (data) {
                console.log(data);
            },
            error: function (error) {
                console.log(error);
            }
        });
    }
};

HTMLElement.prototype.removeClass = function(classname) {
    this.className = this.className.replace(new RegExp(" ?" + classname), "");
};


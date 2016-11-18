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
            Resource.dictionary[name] = data;
            if(callback) callback(data);
            console.info("Finished loading " + name);
        });

    },
    get:function(name){
        return Resource.dictionary[name];
    }
};


var Modal = {
    current:null,
    stack:[],
    modal:null,
    init:function(){
        document.body.innerHTML += "<div id='modal' ></div>";
        Modal.modal = document.getElementById("modal");
    },
    create:function(resourceName, classname, onblur) {
        var modalContents = Resource.get(resourceName);
        if (modalContents != null) {
            Modal.modal.innerHTML = "";
            var newModal = document.createElement("div");
            newModal.innerHTML = modalContents;
            Modal.modal.appendChild(newModal);
            Modal.modal.className = classname;
            Modal.modal.style.visibility = "visible";

            Modal.modal.onclick = function () {
                Modal.hide();
                if (onblur) onblur();
            };
            Modal.current = newModal;
        } else {
            console.error("Resource " + resourceName + " has not been loaded.  Load the resource first with Resource.load()");
        }
    },
    hide:function(){
        if(Modal.current){
            Modal.modal.style.visibility = "hidden";
        }
    },
    show:function(){
        if(Modal.current){
            Modal.modal.style.visibility = "visible";
        }
    },
    destroy:function(){
        if(Modal.current){
            Modal.current = null;
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

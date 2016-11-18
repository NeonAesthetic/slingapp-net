/**
 * Created by ian on 11/12/16.
 */
console.info("sling.js");
var Resource = {
    dictionary:{},
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
    init:function(){
        document.body.innerHTML += "<div id='modal' ></div>";
    },
    create:function(resourceName, classname, onblur) {
        var modalContents = Resource.get(resourceName);
        if (modalContents != null) {
            var modal = document.getElementById("modal");
            modal.className = classname;

            modal.style.visibility = "visible";
            modal.innerHTML = modalContents;
            modal.onclick = function () {
                Modal.hide();
                if (onblur) onblur();
            };
        } else {
            console.error("Resource " + resourceName + " has not been loaded.  Load the resource first with Resource.load()");
        }
    },
    hide:function(){
        if(Modal.current){
            Modal.current.style.visibility = "hidden";
        }
    },
    show:function(){
        if(Modal.current){
            Modal.current.style.visibility = "visible";
        }
    },
    destroy:function(){
        if(Modal.current){
            Modal.current = null;
        }
    }


}

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

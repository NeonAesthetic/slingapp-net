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


function loadResource(){

}
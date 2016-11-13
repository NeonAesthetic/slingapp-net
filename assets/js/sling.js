/**
 * Created by ian on 11/12/16.
 */


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
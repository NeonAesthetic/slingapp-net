/**
 * Created by Ian Murphy on 11/15/2016.
 */
var testConsole = null;
var testList = null;
var TestStatus = {};
var ContextMenu = null;

window.addEventListener("load", function () {
    testConsole = document.getElementById("console");
    testList = document.getElementById("tests");
    testConsole.addEventListener("contextmenu", function (event) {
        contextMenu(event, getConsoleNodeList());
        return false;
    });
});

function runAllTests(){
    var tests = document.getElementsByName("test");
    for(var i = 0; i<tests.length; i++){
        runTest(tests[i]);
    }
}

function runTest(div, callback){
    if(div.className.search(/ disabled/) != -1) return;
    var base = "/testing/test_wrapper.php?test=";
    
    var icoArea = div.getElementsByClassName("ico-area")[0];
    var testScript = div.getAttribute("testfile");
    div.removeClass("list-group-item-success");
    div.removeClass("list-group-item-warning");
    div.removeClass("list-group-item-danger");
    div.className += " running";
    icoArea.innerHTML = '<div class="sling" style=""></div>';
    
    get(base + testScript, "", function (data, responsetype) {
        console.log(responsetype);
        var test = null;
        try{
            test = JSON.parse(data);
        }catch(e){
            console.log(data);
        }


        var newstuff = "<span style=\"color: #fff\">" + testScript + "</span> &middot; ";
        div.removeClass("running");
        if(test && test.success === true){
            newstuff += "<span class='timing-value'> " + test['total-time'] + " ms </span><br>";
            div.className += " list-group-item-success";
            icoArea.innerHTML = "<span class='glyphicon glyphicon-ok' style='color: #33cc33'></span>";
            test['timing'].forEach(function (timeval) {
                newstuff+= (timeval['description'] + " --- <span class='timing-value'>" + timeval['time'] + " ms</span><br>");
            });

        }
        else{
            div.className += " list-group-item-danger";
            icoArea.innerHTML = "<span class='glyphicon glyphicon-remove' style='color: #ff5555'></span>";
        }

        newstuff += test.output + "<hr style='border-color: #444'>";

        document.getElementById("console").innerHTML = newstuff + document.getElementById("console").innerHTML;
        checkChildStatus(div.parentNode)
    });
}

function get(resource, parameters, callback){
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if(xhr.readyState == 4){
            callback(xhr.responseText, xhr.status);
        }
    };
    xhr.open("GET", resource + parameters, true);
    xhr.send();
}

function clearconsole() {
    document.getElementById("console").innerHTML = "";
}

function clearTestStatus(){
    var tests = document.getElementsByClassName("test");
    for(var i = 0; i<tests.length; i++){
        tests[i].removeClass("running");
        tests[i].removeClass("list-group-item-success");
        tests[i].removeClass("list-group-item-danger");
        tests[i].getElementsByClassName("ico-area")[0].innerHTML = "";
    }


}

HTMLElement.prototype.removeClass = function(classname) {
    this.className = this.className.replace(new RegExp(" ?" + classname), "");
};

function refreshTests() {
    var tdiv = document.getElementById("tests");
    tdiv.innerHTML = document.getElementById("spinner").innerHTML;
    get("populate_tests.php", "", function (data, num) {
        tdiv.innerHTML=data;
        addTestButtonEvents();
    });
}


function addTestButtonEvents(){
    var testdiv = document.getElementById("tests");
    var tests = testdiv.querySelectorAll(".test");
    var foldersTestButtons = testdiv.querySelectorAll(".runtest");
    var folderContainers = testdiv.querySelectorAll("test-container");

    tests.forEach(function(n){
        n.addEventListener("click", startTest);
        n.addEventListener("contextmenu", function (event) {

            contextMenu(event, getTestNodeList(n));
            return false;
        });
    });

    foldersTestButtons.forEach(function (n) {
        n.addEventListener("click", runFolderTests);
    })

}


function startTest(){
    runTest(this, null);
}

// NodeList.prototype.forEach = foreach(list, callback){
//     var nodes = [].slice.call(list, 1);
//     nodes.forEach(callback);
// }

function runFolderTests(e){
    // console.log(this);
    e.stopPropagation();
    var folderButton = this.parentNode;
    // console.log(folderButton);
    var containerId = folderButton.getAttribute("container");
    var testContainer = document.getElementById(containerId);
    var tests = testContainer.querySelectorAll(".test");

    tests.forEach(function (test) {
        runTest(test);
    });
}

function checkChildStatus(e){
    var button = document.getElementById(e.getAttribute("button"));
    var children = e.querySelectorAll(".test");
    var numChildren = children.length;
    var numFail = 0;
    for (var i = numChildren -1; i >= 0; i--){

        if(children[i].className.search(/list-group-item-danger/) != -1){
            numFail++;
        }
    }
    // console.log(numChildren, numFail);
    if(numFail == numChildren){
        button.className.replace(/ list-group-item-danger/, "");
        button.className+=" list-group-item-danger";
    }else if(numFail === 0){
        button.className.replace(/ list-group-item-success/, "");
        button.className+=" list-group-item-success";
    }else{
        button.className.replace(/ list-group-item-warning/, "");
        button.className+=" list-group-item-warning";
    }
}

function createMenuLink(text, classname, callback){
    var link = document.createElement("a");
    link.innerHTML = text;
    link.className = classname;
    link.onclick = callback;
    return link;
}

function contextMenu(event, nodelist){
    if(ContextMenu){
        document.body.removeChild(ContextMenu);
        ContextMenu = null;
    }
    event.preventDefault();
    event.stopPropagation();
    ContextMenu = document.createElement("div");
    ContextMenu.className = "context-menu";
    ContextMenu.close = function () {
        document.body.click();
    };
    var menuitemslen = nodelist.length;
    for(var i = 0; i<menuitemslen; i++){
        ContextMenu.appendChild(nodelist[i]);
    }
    document.onclick = function(){
        if(ContextMenu){
            document.body.removeChild(ContextMenu);
            ContextMenu = null;
        }
    };
    ContextMenu.style.left = event.pageX;
    ContextMenu.style.top = event.pageY;
    ContextMenu.onclick = function (event) {
        event.stopPropagation();
    };
    document.body.appendChild(ContextMenu);
}

function getTestNodeList(testElement){
    var list = [];
    var label = document.createElement("p");
    label.innerHTML = testElement.querySelector(".tname").innerHTML;
    var runTest = createMenuLink("Run Test", "", function () {
        testElement.click();
    });

    var disable = null;
    if(testElement.className.search(/disabled/) != -1){
        disable = createMenuLink("Enable Test", "", function () {
            testElement.removeClass("disabled");
            ContextMenu.close();
        });
    }else{
        disable = createMenuLink("Disable Test", "", function () {
            testElement.className += " disabled";
            ContextMenu.close();
        });
    }


    var hr = document.createElement("hr");

    var reload = createMenuLink("Reload Tests", "", function () {
        refreshTests();
        ContextMenu.close();
    });

    return [label, runTest, disable, hr, reload];
}

function getConsoleNodeList(){
    var console = document.createElement("p");
    console.innerHTML = "Test Console";

    var clear = createMenuLink("Clear", "", function () {
        clearconsole();
        ContextMenu.close();
    });

    return [console, clear];
}



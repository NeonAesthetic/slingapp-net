/**
 * Created by Ian Murphy on 11/15/2016.
 */
var testConsole = null;
var Test = {
    log:function (data) {
        testConsole.innerHTML = data + testConsole.innerHTML;
    },
    error:function(data){
        var assertion = "<span class='assert-fail'>" + data + "</span>";
        testConsole.innerHTML = assertion + testConsole.innerHTML;
    }
};

var testList = null;
var TestStatus = {};
var ContextMenu = null;

window.addEventListener("load", function () {
    testConsole = document.getElementById("console");
    testList = document.getElementById("tests");
    testConsole.addEventListener("contextmenu", function (event) {
        ContextMenu.create(event, getConsoleNodeList());
        return false;
    });
});

function runAllTests(){
    var tests = document.getElementsByName("test");
    for(var i = 0; i<tests.length; i++){
        runTest(tests[i]);
    }
}

function failTest(testDiv){
    
}

function createTestOutputContainer(){
    var outputDiv = document.createElement("div");
    outputDiv.className = "test-output-container";
    var beginning = testConsole.firstChild;
    console.log(beginning);
    testConsole.insertBefore(outputDiv, beginning);
    return outputDiv;

}

JSTestOutput = {};
function runJSTest(div, output, callback){
    var base = "/testing/";
    var icoArea = div.getElementsByClassName("ico-area")[0];
    var testScript = div.getAttribute("testfile");
    JSTestOutput[testScript] = "";
    var preparation = document.createElement("div");
    var passed = false;

    output.innerHTML += testScript + "<br>";

    get(base + testScript, "", function (data, responsetype) {
        preparation.innerHTML = data;
        data = preparation.querySelector("script").innerHTML;
        var logger = {};
        logger.log = function(msg){
            output.innerHTML+=msg + "<br>";
        };
        try{
            eval(data);
            test(logger);

            passed = true;
        }catch(exception){
            console.log(exception);
        }finally {

        }
        // console.log(output, passed);
        evalTest(div, passed, null, callback);

    });

}

function runPHPTest(div, output, callback){
    var base = "/testing/test_wrapper.php?test=";
    var testScript = div.getAttribute("testfile");
    var icoArea = div.getElementsByClassName("ico-area")[0];
    var passed = false;
    get(base + testScript, "", function (data, responsetype) {
        console.log(responsetype);
        var test = null;
        try{
            test = JSON.parse(data);
            if(test){
                output.innerHTML += test.output;
                if(test.success)
                    passed = true;
            }
            console.log(test);
        }catch(e){
            output.innerHTML += data;
            passed = false;
        }
        evalTest(div, passed, callback );


    });
}

function runTest(div, callback){
    if(div.className.search(/ disabled/) != -1) return;
    var icoArea = div.getElementsByClassName("ico-area")[0];
    div.removeClass("list-group-item-success");
    div.removeClass("list-group-item-warning");
    div.removeClass("list-group-item-danger");
    div.className += " running";
    icoArea.innerHTML = '<div class="sling" style=""></div>';

    var testName = div.querySelector(".tname").innerHTML;

    var output = createTestOutputContainer();
    output.innerHTML+= "<span style='color: #fefefe'>" + testName + "</span> &middot; " + div.getAttribute("testfile") + "<br>";
    if(div.className.search(/test-js/) != -1){
        runJSTest(div, output, callback);
    }else{
        runPHPTest(div, output, callback);
    }
    

}

function evalTest(div, passed, callback){

    var icoArea = div.getElementsByClassName("ico-area")[0];
    var newstuff = "";
    div.removeClass("running");
    if(passed){
        div.className += " list-group-item-success";
        icoArea.innerHTML = "<span class='glyphicon glyphicon-ok' style='color: #33cc33'></span>";
    }
    else{
        div.className += " list-group-item-danger";
        icoArea.innerHTML = "<span class='glyphicon glyphicon-remove' style='color: #ff5555'></span>";
    }

    checkChildStatus(div.parentNode);
    if(callback)callback();
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
    var folderContainers = testdiv.querySelectorAll(".test-folder");

    tests.forEach(function(n){
        n.addEventListener("click", startTest);
        n.addEventListener("contextmenu", function (event) {
            ContextMenu.create(event, getTestNodeList(n));
            return false;
        });
    });

    folderContainers.forEach(function (c) {
        c.addEventListener("contextmenu", function (event) {
            ContextMenu.create(event, getFolderNodeList(c));
        });
    });

    foldersTestButtons.forEach(function (n) {
        n.addEventListener("click", runFolderTests);
    })

}

function runTestsInSequence(testList){
    var test = testList[testList.length-1];
    runTest(test, function () {
        runTestsInSequence(test)
    })
}

function startTest(){
    runTest(this, null);
}



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
    if(!button) return;
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


function getTestNodeList(testElement){
    var label = document.createElement("p");
    label.innerHTML = testElement.querySelector(".tname").innerHTML;
    var runTest = ContextMenu.createMenuLink("Run Test", "", function () {
        testElement.click();
    });

    var disable = null;
    if(testElement.className.search(/disabled/) != -1){
        disable = ContextMenu.createMenuLink("Enable Test", "", function () {
            testElement.removeClass("disabled");
            ContextMenu.close();
        });
    }else{
        disable = ContextMenu.createMenuLink("Disable Test", "", function () {
            testElement.className += " disabled";
            ContextMenu.close();
        });
    }


    var hr = document.createElement("hr");

    var reload = ContextMenu.createMenuLink("Reload Tests", "", function () {
        refreshTests();
        ContextMenu.close();
    });

    return [label, runTest, disable, hr, reload];
}

function getConsoleNodeList(){
    var console = document.createElement("p");
    console.innerHTML = "Test Console";

    var clear = ContextMenu.createMenuLink("Clear", "", function () {
        clearconsole();
        ContextMenu.close();
    });

    return [console, clear];
}

function getFolderNodeList(f){
    var name = f.querySelector(".tname").innerHTML;
    var label = ContextMenu.createLabel(name + " Folder");
    var runtests = ContextMenu.createMenuLink("Run Contained Tests", "", function () {
        f.querySelector(".runtest").click();
    });

    return [label, runtests];
}

function assert(stuff, comment){
    if(!stuff){
        console.log("<span class='assert-fail'>Assert failed: " + comment + "</span>");
    }
}



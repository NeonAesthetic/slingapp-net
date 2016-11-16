/**
 * Created by Ian Murphy on 11/15/2016.
 */



function runAllTests(){
    var tests = document.getElementsByName("test");
    for(var i = 0; i<tests.length; i++){
        runTest(tests[i]);
    }
}

function runTest(div, callback){
    var base = "/testing/test_wrapper.php?test=";
    
    var icoArea = div.getElementsByClassName("ico-area")[0];
    var testScript = element.getAttribute("testfile");
    div.className += " running";
    icoArea.innerHTML = '<div class="sling" style=""></div>';
    
    get(base + testScript, "", function (data, responsetype) {
        var test = JSON.parse(data);

        var newstuff = "<span style=\"color: #fff\">" + testScript + "</span> &middot; ";
        div.className = div.className.replace(/ running/, "");
        if(test.success === true){
            div.className += " list-group-item-success";
            icoArea.innerHTML = "<span class='glyphicon glyphicon-ok' style='color: #33cc33'></span>";
        }
        else{
            div.className += " list-group-item-danger";
            icoArea.innerHTML = "<span class='glyphicon glyphicon-remove' style='color: #ff5555'></span>";
        }

        newstuff += test.output + "<hr style='border-color: #444'>";

        document.getElementById("console").innerHTML = newstuff + document.getElementById("console").innerHTML;
        callback(test.success);
    })
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
    this.className = this.className.replace(new RegExp(classname), "");
};

function refreshTests() {
    var tdiv = document.getElementById("tests");
    tdiv.innerHTML = document.getElementById("spinner").innerHTML;
    get("populate_tests.php", "", function (data, num) {
        tdiv.innerHTML=data;
        addRTButtonListener();
    });
}

function runContainingTests(event, e){
    event.stopPropagation();
    e= e.parentNode.parentNode;
    console.log(e);
    var id = e.getAttribute("href").slice(1);
    console.log(id);
    var tests = document.getElementById(id).querySelectorAll(".list-group-item");
    function testCounter(passed) {
        var numTests = tests.length;
        var parentDiv = e;
        console.log(passed, numTests);
        testCounter.total = ++testCounter.total || 1;
        if(passed)
            testCounter.passed = ++testCounter.passed || 1;
        if(testCounter.total === numTests)
        {
            console.log("Here");
            if(testCounter.passed === testCounter.total){
                parentDiv.className += " list-group-item-success";
            }else if(!testCounter.passed){
                parentDiv.className += " list-group-item-danger";
            }else{
                parentDiv.className += " list-group-item-warning";
            }
        }
    }
    console.log(tests);
    for(var i = 0; i<tests.length; i++){
        runTest(tests[i], testCounter);
    }

    return false;
}


function addRTButtonListener(){
    var rtbuttons = document.getElementsByClassName("runtest");
    var len = rtbuttons.length;
    for(var i = 0; i<len; i++){
        rtbuttons[i].addEventListener("click", function(event){
            event.stopPropagation();
            var folder = this.parentNode;
            var contents = document.getElementById(folder.getAttribute("href").slice(1)).querySelectorAll(".test");
            var len = contents.length;
            for (var i = 0; i<len;i++){
                contents[i]
            }
        });
    }
}
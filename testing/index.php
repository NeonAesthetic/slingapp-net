<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 9/5/2016
 * Time: 1:08 PM
 */

session_start();
?>

<html xmlns="http://www.w3.org/1999/html">
<head>
    <title>Sling Testing</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/assets/css/tests.css">

</head>
<body style="background-color: #eee">
<div class="" style="margin: 0 auto; position: relative; left: 0; right: 0; max-width: 1200px; padding: 5px" >
    <div class="card" style="width: calc(100% - 10px);padding: 7px; margin: 5px;">
        <button class="btn btn-circle" onclick="clearconsole()"><span title="Clears the Console window" class="glyphicon glyphicon-console"></span> Clear Console </button>
        <button class="btn btn-circle" onclick="clearTestStatus()"><span title="Clears the status of all tests" class="glyphicon glyphicon-menu-hamburger"></span> Clear Test Status </button>
        <button class="btn btn-circle" onclick="refreshTests()"><span title="Refresh Test List" class="glyphicon glyphicon-refresh"></span> Repopulate Tests </button>

    </div>
<!--    <div class="card" style="width: 100%; ">-->
<!--        <center>-->
<!--            <h1 style="font-size: 2.5em; font-weight: 100">Sling Testing</h1>-->
<!--        </center>-->
<!---->
<!--    </div>-->
    <div class="card" style="max-width: calc(35% - 10px); margin: 5px;">
        <div class="list-group">
            <a onclick="runAllTests()" href="#" class="list-group-item list-group-item-heading"><b>Run all tests</b></a>
            <div id="tests">
                <div class="spinner">
                    <div class="rect2"></div>
                    <div class="rect3"></div>
                    <div class="rect4"></div>
                    <div class="rect5"></div>
                </div>
            </div>
        
        </div>
    </div>
    <div id="console" class="card" style="width: calc(65% - 10px);color: #CCC; background-color: #333; font-family: monospace; min-height: 70vh; overflow-y: scroll; max-width: 800px; margin: 5px;">
    </div>
</div>
<div id="spinner" style="visibility: hidden;">
    <div class="spinner">
        <div class="rect2"></div>
        <div class="rect3"></div>
        <div class="rect4"></div>
        <div class="rect5"></div>
    </div>
</div>
</body>
<script>
    window.addEventListener("load", function () {
        refreshTests();
    });

    function runAllTests(){
        var tests = document.getElementsByName("test");
        for(var i = 0; i<tests.length; i++){
            runTest(tests[i]);
        }
    }

    function runTest(element){
        var base = "/testing/test_wrapper.php?test=";
        var div = element;
        var icoArea = div.getElementsByClassName("ico-area")[0];
        var testScript = element.getAttribute("testfile");
        div.className = "list-group-item running";
        icoArea.innerHTML = '<div class="sling" style=""></div>';
        get(base + testScript, "", function (data, responsetype) {
            var newstuff = "";
            newstuff += "<span style=\"color: #fff\">" + testScript + "</span> &middot; ";
            div.className = div.className.replace(/ running/, "");
            if(data.search(/test-fail/) != -1){
                div.className += " list-group-item-danger";
                icoArea.innerHTML = "<span class='glyphicon glyphicon-remove' style='color: #ff5555'></span>";
            }
            else if(data.search(/test-pass/) != -1){
                div.className += " list-group-item-success";
                icoArea.innerHTML = "<span class='glyphicon glyphicon-ok' style='color: #33cc33'></span>";
            }

            newstuff+= data + "<hr style='border-color: #444'>";

            document.getElementById("console").innerHTML = newstuff + document.getElementById("console").innerHTML
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
        var tests = document.getElementsByName("test");
        tests.forEach(function (t) {
            t.className = "list-group-item";
            t.getElementsByClassName("ico-area")[0].innerHTML = "";
        })
    }
    
    function refreshTests() {
        var tdiv = document.getElementById("tests");
        tdiv.innerHTML = document.getElementById("spinner").innerHTML;
        get("populate_tests.php", "", function (data, num) {
            tdiv.innerHTML=data;
        });

    }

    function runContainingTests(event, e){
        event.stopPropagation();
        e= e.parentNode.parentNode;
        console.log(e);
        var id = e.getAttribute("href").slice(1);
        console.log(id);
        var tests = document.getElementById(id).querySelectorAll(".list-group-item");
        console.log(tests);
        for(var i = 0; i<tests.length; i++){
            runTest(tests[i]);
        }
        
        return false;
    }


</script>
<script type='text/javascript' src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.1.1.min.js"></script>
<script type='text/javascript' src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

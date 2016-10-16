<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 9/5/2016
 * Time: 1:08 PM
 */

?>

<html>
<head>
    <title>Sling Testing</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/assets/css/tests.css">

</head>
<body style="background-color: #eee">

<div class="container">
    <div class="col-lg-12">
        <div class="card">
            <center>
                <h1 style="font-size: 3.5em; font-weight: 100">Sling Testing</h1>
            </center>

        </div>

    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="list-group">
                <a onclick="runAllTests()" href="#" class="list-group-item list-group-item-heading"><b>Run all tests</b></a>
            <?php
            $files = glob("./tests/*");
            foreach ($files as $testfile){
                $text = fread(fopen($testfile, "r"), filesize($testfile));
                preg_match("#(?<=testname:)([ ]?)[a-zA-Z0-9 ]+?(?=\n)#", $text, $match);
                $test_name = $match[0];
                if(strpos($test_name, "NOINCLUDE") !== false) continue;
                preg_match("#(?<=testdesc:).+(?=\n)#", $text, $matches);
                $test_desc = $matches[0];

                echo "<a href='#' title=\"" . $test_desc . "\" name=\"test\" testfile='". basename($testfile) . "' onclick='runTest(this)' class='list-group-item'>" . $test_name . " - " . basename($testfile) . "</a>";
            }
            ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div id="console" class="card" style="color: #CCC; background-color: #333; font-family: monospace; height: 70vh; overflow-y: scroll;">
        </div>
    </div>
</div>
</body>
<script>
    function runAllTests(){
        var tests = document.getElementsByName("test");
        for(var i = 0; i<tests.length; i++){
            runTest(tests[i]);
        }
    }

    function runTest(element){
        var base = "/testing/test_wrapper.php?test=";
        var div = element;
        var testScript = element.getAttribute("testfile");
        div.className = "list-group-item running";
        get(base + testScript, "", function (data, responsetype) {
            var newstuff = "";
            newstuff += "<span style=\"color: #fff\">" + testScript + "</span> &middot; ";
            div.className = div.className.replace(/ running/, "");
            if(data.search(/test-fail/) != -1){
                div.className += " list-group-item-danger";
            }
            else if(data.search(/test-pass/) != -1){
                div.className += " list-group-item-success";
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
</script>

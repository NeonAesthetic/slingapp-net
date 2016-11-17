<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 9/5/2016
 * Time: 7:32 PM
 */

assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_QUIET_EVAL, 0);

$GLOBALS['TEST_FAILED'] = false;

set_include_path(realpath($_SERVER["DOCUMENT_ROOT"]) . "/assets/php/");

function create_test_json(){
    $GLOBALS['json'] = [];
    $GLOBALS['json']["type"] = "Test Results";
    $GLOBALS['json']["success"] = false;
    $GLOBALS['json']["total-time"] = 0;
    $GLOBALS['json']["timing"] = [];
    $GLOBALS['json']["output"] = null;
}


function test_end(){
    $GLOBALS["json"]["output"] = ob_get_clean();
    if(function_exists("cleanup"))
        call_user_func("cleanup");
}

function fail_test(){
    $GLOBALS['TEST_FAILED'] = true;
}

function assert_handler($file, $line, $code, $desc = null)
{
    echo "<span class='assert-fail'>Assertion failed at " . basename($file) . ":$line</span><br>";
    echo "\"$desc\"";
    echo "<br>";
    fail_test();
};

function get_fatal_error(){
    $error = error_get_last();
    $str = null;
    if($error)
        $str = "PHP Fatal Error on line " . $error["line"] . " in file " . $error['file'] . ": " . $error['message'];
    return $str;
}


register_shutdown_function(function (){
    test_end();
    $error = get_fatal_error();
    if($error){
        $GLOBALS['json']['output'] .= $error;
        fail_test();
    }
    if($GLOBALS['TEST_FAILED']){
        $GLOBALS["json"]["success"] = false;
    }else{
        if(isset($GLOBALS['RunTime']))
            $ms = round($GLOBALS['RunTime']*1000, 3);
        $GLOBALS["json"]["success"] = true;
    }
    echo json_encode($GLOBALS["json"]);


});

assert_options(ASSERT_CALLBACK, 'assert_handler');

set_exception_handler(function(Throwable $exception){
    echo "PHP Exception on line " . $exception->getLine() . " of " . $exception->getFile() . ". " . $exception->getMessage() . "<br>";
    fail_test();
});

set_error_handler(function($errno, $errstr, $errfile, $errline){
    echo "PHP Error: " . $errstr . " at line " . $errline . " in " . $errfile . "<br>";
    fail_test();
});



function mark($comment = null){
    static $start = 0;
    if($comment == null){
        $start = microtime(true);
    }else{
        $end = microtime(true);
        $elapsed = ($end - $start);
        $GLOBALS["json"]["timing"][] = ["description" => $comment,
                                        "time" => round($elapsed * 1000, 3)];
        $start = $end;
    }
}

if(isset($_GET['test'])){
    ob_start();
    $start = microtime(true);
    create_test_json();
    try{
        include(realpath($_SERVER['DOCUMENT_ROOT']) . "/testing/". $_GET["test"]);
    }catch (Throwable $e){
        echo "REEEE";
    }
    $end = microtime(true);
    $GLOBALS["json"]["total-time"] = round(($end - $start)*1000, 3);
}else{
    echo "Test file name was not provided<br>";
}

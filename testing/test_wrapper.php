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
    $json = [];
    $GLOBAL['timing'] = [];
    $json["type"] = "Test Results";
    $json["success"] = false;
    $json["total-time"] = 0;
    $json["timing"] = [];
    $json["output"] = null;
    return $json;
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

function recurse_backtrace(array $bt, $level){
    $indent = str_repeat("&nbsp;", $level * 2);
    foreach ($bt as $key=>$value){
        if(is_array($value)){
            echo $indent . $key . ":<br>";
            recurse_backtrace($value, $level + 1);
        }else{
            echo $indent . $key . ": " . $value . "<br>";
        }
    }
}

register_shutdown_function(function (){
    test_end();
    if($GLOBALS['TEST_FAILED']){
        $GLOBALS["json"]["success"] = false;
    }else{
        $ms = round($GLOBALS['RunTime']*1000, 3);
        $GLOBALS["json"]["success"] = true;
    }
    echo json_encode($GLOBALS["json"]);


});

assert_options(ASSERT_CALLBACK, 'assert_handler');

set_exception_handler(function(Throwable $exception){
    echo "PHP Exception on line " . $exception->getLine() . " of " . $exception->getFile() . ". " . $exception->getMessage() . "<br>";
//    var_dump($exception->getTrace());

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
    $GLOBALS["json"] = create_test_json();
    $start = microtime(true);
    include(realpath($_SERVER['DOCUMENT_ROOT']) . "/testing/". $_GET["test"]);
    $end = microtime(true);
    $GLOBALS["RunTime"] = ($end - $start);
}else{
    echo "Test file name was not provided<br>";
}

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
set_include_path(realpath($_SERVER["DOCUMENT_ROOT"]) . "/assets/php/classes/");

function test_end(){
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

register_shutdown_function(function (){
    test_end();
    if($GLOBALS['TEST_FAILED']){
        echo "<span class='test-fail'>Test Failed</span><br>";
        echo ob_get_clean();
        echo $GLOBALS["TEST_OUTPUT"];
    }else{
        echo "<span class='test-pass'>Test Passed</span><br>";
        echo $GLOBALS["TEST_OUTPUT"];
    }


});
assert_options(ASSERT_CALLBACK, 'assert_handler');

set_exception_handler(function(Throwable $exception){
    echo "PHP Exception on line " . $exception->getLine() . " of " . $exception->getFile() . ". " . $exception->getMessage();
    fail_test();
});
set_error_handler(function($errno, $errstr, $errline, $errfile){
    if($errno == E_USER_ERROR) trigger_error("Test Failed", E_CORE_ERROR);
    echo "PHP Error: " . $errstr . " at line " . $errline . " in " . $errfile . "<br>";
    fail_test();
});

if(isset($_GET['test'])){
    ob_start();

    include(realpath($_SERVER['DOCUMENT_ROOT']) . "/testing/tests/". $_GET["test"]);

    $GLOBALS['TEST_OUTPUT'] = ob_get_clean();
    test_end();
}else{
    echo "Test file name was not provided<br>";
}

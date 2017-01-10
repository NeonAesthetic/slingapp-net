<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 11/8/16
 * Time: 8:24 AM
 * Test Name: Test GetParams
 * Description: ensures getParams works
 */

include_once realpath($_SERVER["DOCUMENT_ROOT"]) . "/assets/php/components/StandardHeader.php";

$_POST["Test1"] = "TestValue1";

sleep(2);

$p = GetParams("Test1", "Test2");
var_dump($p);
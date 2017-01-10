<?php



/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 9/6/2016
 * Time: 9:00 AM
 *
 * Test Name: Test Fail
 * Description: Purposely fails
 */


//echo ("Asserting that 1 is equal to 2") . "<br>";
assert(1==2, "Assert that 1 is 2");



cleanup();

function cleanup(){
    try{

    }catch (Exception $e){

    }
}



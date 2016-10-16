<?php

//testname: Test Fail
//testdesc: Purposely fails

/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 9/6/2016
 * Time: 9:00 AM
 */

echo ("Asserting that 1 is equal to 2");
assert(1==2, "Assert that 1 is 1");

cleanup();

function cleanup(){
    try{

    }catch (Exception $e){

    }
}



<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/16/16
 * Time: 5:57 PM
 */

//Include directory
//***************************
//      Basic Setup
//...........................
//
//  Import constants file?
//  
//

set_include_path(realpath($_SERVER['DOCUMENT_ROOT']) . "/assets/php");

function GetParams(...$params){
    $parameters = [];
    foreach ($params as $pname){
        $parameters[$pname] = isset($_POST[$pname]) ? $_POST[$pname] : (isset($_GET[$pname]) ? $_GET[$pname] : null);
    }
    return $parameters;
}
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

require_once "classes/Database.php";
require_once "classes/logging/Logger.php";


function GetParams(...$params){
    $parameters = [];
    foreach ($params as $pname){
        $parameters[$pname] = isset($_POST[$pname]) ? $_POST[$pname] : (isset($_GET[$pname]) ? $_GET[$pname] : null);
    }
    return $parameters;
}

function ApacheError($number){
    http_response_code($number);
    include "components/error_pages/$number.php";
}
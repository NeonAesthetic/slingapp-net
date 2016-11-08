<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 11/8/16
 * Time: 8:19 AM
 */


require_once realpath($_SERVER["DOCUMENT_ROOT"]) . "/assets/php/components/StandardHeader.php";
require_once "classes/Account.php";

$parameters = GetParams("action", "email", "fname", "lname", "password");

switch ($parameters["action"]){
    case "register":{
        
        break;
    }
    case "login":{
        break;
    }
    case "getcookie":{
        break;
    }
    case "newtoken":{
        break;
    }
}
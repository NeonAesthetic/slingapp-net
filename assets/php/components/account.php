<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 11/8/16
 * Time: 8:19 AM
 */


require_once realpath($_SERVER["DOCUMENT_ROOT"]) . "/assets/php/components/StandardHeader.php";
require_once "classes/Account.php";

$p = GetParams("action", "email", "fname", "lname", "password");

switch ($p["action"]){
    case "register":{
        Account::CreateAccount($p["email"], $p["fname"], $p["lname"], $p["password"]);
        break;
    }
    case "login":{
        break;//pass in token and return JSON account object
    }
    case "getcookie":{
        break;
    }
    case "newtoken":{
        break;
    }
}
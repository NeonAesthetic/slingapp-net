<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 11/20/2016
 * Time: 1:46 PM
 */

require_once realpath($_SERVER["DOCUMENT_ROOT"]) . "/assets/php/components/StandardHeader.php";
require_once "classes/Account.php";
$p = GetParams("action", "email", "fname", "lname", "password", "token");

switch($p['action']){
    case "login":
    {
        $account = Account::Login($p['token']);
        echo $account->getJSON();
    }break;
}
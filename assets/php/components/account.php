<?php
/**
 * Created by PhpStorm.
 * User: Ian, Isaac
 * Date: 11/8/16
 * Time: 8:19 AM
 */

require_once "components/StandardHeader.php";
require_once "classes/Account.php";

$p = GetParams("action", "email", "fname", "lname", "pass1", "pass2", "token");

switch ($p['action']) {
    case "roomdata":
        $account = Account::Login($p['token']);
        echo($account->getRoomsUserIsIn());
        break;
    case "register":
        if (!Account::CheckDatabase($p['email'])) {
            $account = Account::CreateAccount($p['email'], $p['fname'], $p['lname'], $p['pass1'], $p['token']);
            echo (method_exists($account, "getJSON")) ? $account->getJSON() : $account;
        } else {
            echo json_encode(['error' =>"Email has already been registered"]);
        }
        //if method getJSON exists, then an account has been created, otherwise it returned a JSON error message

        break;

    case "login":
        $account = Account::Login($p['email'], $p['pass1']);
        echo (method_exists($account, "getJSON")) ? $account->getJSON() : json_encode([
            "error"=>"Invalid email or password"
        ]);
        break;

    case "changepass":
            $account = Account::Login($p['token']);
            echo ($account->updatePass($p['pass1'])) ? $account->getJSON() : $account;
        break;

    case "deactivate":
        $account = Account::Login($p['token']);
        echo ($account->Deactivate()) ? $account->getJSON() : $account;
        break;

    case "nocookie":
        $account = Account::CreateAccount();
        echo $account->getJSON();
        break;

    case "tokenisvalid":
        echo (Account::Login($p['token']) != false) ?
            json_encode(["valid" => true]) : json_encode(["valid" => false]);
        break;

    default:
        echo null;  //return a non JSON object to trigger an AJAX failure
}
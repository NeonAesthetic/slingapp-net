<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 4/9/2017
 * Time: 2:25 PM
 */
require_once "classes/Room.php";
require_once "classes/Account.php";

$invite_code = $_GET['invite'];
$room = Room::GetFromCode($invite_code);
if(!$room){
    header("Location: /");
}
$token = $_COOKIE['Token'];
$account = NULL;
if($token) $account = Account::Login($token);

if(!$account){
    $account = Account::CreateAccount();
    setcookie("Token", $account->getToken(), time() + 60 * 60 * 24 * 7, "/");
}

$room->addParticipant($account);

header("Location: /rooms/" . $room->getRoomID());


<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 11/20/2016
 * Time: 1:00 PM
 * Test Name: Create Room Get JSON
 */

require_once "classes/Room.php";

$account = Account::CreateAccount();
$token = $account->getToken();
$room = Room::createRoom("Test Room", $token);
//var_dump($room->getJSON(true)["Accounts"]);

$room->addParticipant($token);

echo count($room->getAccounts());

//echo $room->getJSON();
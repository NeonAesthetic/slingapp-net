<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 4/7/2017
 * Time: 8:53 AM
 * Test Name: Basic Room2 Tests
 */

require_once "classes/Room2.php";
require_once "classes/Account.php";

$room = Room2::createRoom("Test Name");
$room_id = $room->getRoomID();

$room = new Room2($room_id);

echo $room->getID() . BR;

echo 

var_dump($room);
<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 11/23/16
 * Time: 10:11 AM
 */
require_once "RoomSocketServer.php";

//error_log("HERE");
$server = new RoomSocketServer("0.0.0.0", "8001");
$server->run();
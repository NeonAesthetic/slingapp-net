<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 12/1/16
 * Time: 5:01 PM
 * Test Name: Test Message Dump
 */

require_once "classes/Chat.php";

$chat = new Chat('14941480636805221883');
$chat->getMessages(500);
echo json_encode($chat->_messages);
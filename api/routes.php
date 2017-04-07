<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 3/23/2017
 * Time: 11:36 AM
 */


const API_ROUTES = [
    ["^$", "api.php", "index"],
    ["^room/([a-z]+)$", "room.php", "no_room_action"],
    ["^room/([0-9]+)/([a-z]+)$", "room.php", "room_action"],
    ["^room/create/(.+)?$", "room.php", "create_room_and_join_account"] //POST keys required: account_id, room_name
];
<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 3/23/2017
 * Time: 11:36 AM
 */


const API_ROUTES = [
    ["^$", "api.php", "index"],
    ["^room/new/(.+)?$", "room.php", "create_room_and_join_account"],
    ["^room/([0-9]+)$", "room.php", "room_view"],
    ["^me/?$", "account.php", "me"],
    ["^user/new/?", "account.php", "create_blank_account"],
    ["^user/([0-9]+)/?", "account.php", "user_view"],
];
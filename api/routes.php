<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 3/23/2017
 * Time: 11:36 AM
 */


const API_ROUTES = [
    ["^$", "api.php", "index"],
    ["^feed/([0-9]+)/?$", "api.php", "dev_feed"],
    ["^commits/([0-9]+)/?$", "api.php", "commit_feed"],
    ["^room/new/([^.]+)/?$", "room.php", "create_room_and_join_account"],
    ["^room/([0-9]+)/?$", "room.php", "room_view"],
    ["^room/join/([0-9a-zA-Z]+)/?$", "room.php", "join_existing_room"],
    ["^room/([0-9]+)/invite/?$", "room.php", "room_create_invite"],
    ["^room/([0-9]+)/delete/?$", "room.php", "delete_room"],
    ["^me/?$", "account.php", "me"],
    ["^user/new/?", "account.php", "create_blank_account"],
    ["^user/([0-9]+)/?", "account.php", "user_view"],
    ["^user/authenticate/?", "account.php", "authenticate"],
    ["^user/register/?", "account.php", "register"],
    ["^metrics/?", "api.php", "website_health"],
    ["^room/([0-9]+)/invite/?$", "room.php", "room_create_invite"],
];
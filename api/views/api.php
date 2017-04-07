<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 3/23/2017
 * Time: 12:46 PM
 */

require_once "classes/http/HTTPResponse.php";



function index(){
    return new HTTPResponse([
        "api_version"=>"1.0.0",
        "available_base_routes"=>[
            "room",
            "user"
        ]
    ]);
}
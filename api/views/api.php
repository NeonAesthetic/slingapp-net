<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 3/23/2017
 * Time: 12:46 PM
 */

require_once "classes/http/HTTPResponse.php";
require_once "classes/logging/PerformanceMetricsLogger.php";



function index(){
    return new HTTPResponse([
        "api_version"=>"1.0.0",
        "available_base_routes"=>[
            "room",
            "user"
        ]
    ]);
}

function dev_feed($num){
    $feed = implode(file('https://github.com/NeonAesthetic/slingapp-net/commits/dev.atom'));
    $xml = simplexml_load_string($feed);
    $json = json_encode($xml);
    $array = json_decode($json,TRUE);
    return new HTTPResponse(get_last_num_entries($array, $num));
}


function get_last_num_entries($object, $num){
    $results = [];
    for ($i = 0; $i<$num;$i++){
        $entry = $object['entry'][$i];
        $results[] = $entry;
    }
    return $results;
}

function website_health(){
    return new HTTPResponse(PerformanceMetricsLogger::GetRecentMetrics());
}
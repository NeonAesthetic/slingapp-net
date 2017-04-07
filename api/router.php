<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 3/23/2017
 * Time: 11:22 AM
 */


$script_start = microtime(true);
require_once "routes.php";
require_once "classes/http/HTTPResponse.php";
$requested_resource = $_GET['resource'];

foreach (API_ROUTES as $route) {
    $pattern = $route[0];
    $file    = $route[1];
    $view    = $route[2];
    if(preg_match("#" . $pattern . "#", $requested_resource, $matches)){
        $parameters = array_slice($matches, 1);
        ob_start();
        require_once "./views/" . $file;
        $output = call_user_func_array($view, $parameters);
        ob_get_clean();
        if(!is_a($output, "HTTPResponse")){
            $error_message = "View '$view' does not return an HTTPResponse object.";
            echo $error_message;
            http_response_code(500);
            throw new Exception($error_message);
        }else{
            echo $output;
        }
        $script_end = microtime(true);
        error_log("[API] Access Time: " . round(($script_end - $script_start)*1000, 2) . "ms");
        exit();
    }

}

echo new HTTPResponse([
    "status"=>404,
    "error"=>"Route not found"
], 404);


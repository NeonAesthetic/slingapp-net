<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 3/23/2017
 * Time: 12:02 PM
 */




class HTTPResponse
{
    private $__output = "";
    private $__is_json = false;
    private $__status;
    public function __construct($string, $status=200)
    {
        $this->__status = $status;
        if(is_array($string)){
            $this->__output = json_encode($string, JSON_PRETTY_PRINT);
            $this->__is_json = true;
        }else{
            $this->__output = $string;
        }
    }

    public function status($status_number){
        $this->__status = $status_number;
        return $this;
    }

    public function __toString()
    {
        if($this->__is_json) header('Content-Type: application/json');
        http_response_code($this->__status);
        return $this->__output;
    }

    public static function Render($template_path, $context){
        ob_start();
        extract($context);
        require_once $template_path;
        return new HTTPResponse(ob_get_clean());
    }

}
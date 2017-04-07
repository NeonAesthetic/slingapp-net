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
    public function __construct($string)
    {
        if(is_array($string)){
            $this->__output = json_encode($string, JSON_PRETTY_PRINT);
            $this->__is_json = true;
        }else{
            $this->__output = $string;
        }
    }

    public function __toString()
    {
        if($this->__is_json) header('Content-Type: application/json');
        return $this->__output;
    }

    public static function Render($template_path, $context){
        ob_start();
        extract($context);
        require_once $template_path;
        return new HTTPResponse(ob_get_clean());
    }

}
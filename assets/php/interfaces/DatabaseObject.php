<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/27/16
 * Time: 1:24 PM
 */

set_include_path(realpath($_SERVER["DOCUMENT_ROOT"]) . "/assets/php/");
require_once "classes/Database.php";

abstract class DatabaseObject
{

    protected $_has_changed;
    public function hasChanged(){
        return $this->_has_changed;
    }

    public abstract function delete();
    //deletes object from database

    public abstract function update();

    public abstract function getJSON();
    
    public static function Log($action, $description){
        $file = basename(__FILE__);
        $ip = $_SERVER["REMOTE_ADDR"];
        $sql = "INSERT INTO Logs (IP, File, Action, Description) VALUES(:ip, :file, :action, :desc)";
        Database::connect()->prepare($sql)->execute([
            ":ip"=>$ip,
            ":file"=>$file,
            ":action"=>$action,
            ":desc"=>$description
        ]);
    }
    
}
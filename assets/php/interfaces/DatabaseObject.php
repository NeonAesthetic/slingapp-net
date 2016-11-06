<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/27/16
 * Time: 1:24 PM
 */

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
    
    public static function Log($classname, $action, $description){
        
    }
}
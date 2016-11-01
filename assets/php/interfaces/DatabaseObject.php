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
    //deletes object from database

    public function hasChanged(){
        return $this->_has_changed;
    }
    public abstract function delete();

    protected function delete_components(){
//        foreach ($this->_components as &$component) {
//            $component->delete();
//        }
//        $this->_components = [];
    }

    protected function getComponents(){
        return $this->_components;
    }

    public abstract function update();

    protected function updateComponents(){
        foreach ($this->_components as &$component) {
            if ($component->hasChanged())
                $component->update();
        }
    }

}
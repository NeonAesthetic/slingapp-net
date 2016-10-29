<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/27/16
 * Time: 1:20 PM
 */
class RoomCode extends DatabaseObject
{
    private $_code;
    private $_roomid;
    private $_creator;
    
    public function __construct($code, $roomid, $creator)
    {
        $this->_code = $code;
        $this->_roomid = $roomid;
        $this->_creator = $creator;
    }
    
    public function delete(){
        
    }
}
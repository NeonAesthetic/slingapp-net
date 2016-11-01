<?php

/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 10/27/16
 * Time: 1:20 PM
 */
class RoomCode extends Database
{
    private $_code;
    private $_roomID;
    private $_creator;

    public function __construct($code, $roomID, $creator)
    {
        $this->_code = $code;
        $this->_roomid = $roomID;
        $this->_creator = $creator;
    }

    public function generate()
    {
        return (rand(1000, 9999) . rand(1000, 9999));
    }
    
    public function delete($code)
    {
        
    }
    
}
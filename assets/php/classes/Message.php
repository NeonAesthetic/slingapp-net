<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 12/1/16
 * Time: 3:54 PM
 */
class Message
{
    public $id;
    public $author;
    public $content;
    public $room;
    public function __construct($id, $room, $author, $content)
    {
        $this->author = $author;
        $this->id = $id;
        $this->room = $room;
        $this->content = $content;
    }
}
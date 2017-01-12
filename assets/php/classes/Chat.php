<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 12/1/16
 * Time: 3:53 PM
 */

require_once "Message.php";
require_once "classes/Database.php";

class Chat
{
    public $_messages = [];
    protected $_roomid;
    public function __construct($roomid)
    {
        $this->_roomid = $roomid;
    }

    private function _get_messages($number, $before = -1){

    }

    public function getMessages($number, $before = 99999999999999999999999999){
        $sql = "SELECT * 
                FROM Messages 
                WHERE RoomID = :rid AND MessageID < :bef
                ORDER BY MessageID DESC
                LIMIT 500";
        $statement = Database::connect()->prepare($sql);
        if(!$statement->execute([
            ":rid"=>$this->_roomid,
            ":bef"=>$before
        ])){

        };
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as $message){
            $this->_messages[$message["MessageID"]] = new Message($message["MessageID"], $message["RoomID"], $message["AccountID"], $message["Content"]);
        }
    }

    public function addMessage($id, $room, $author, $content){
        $sql = "INSERT INTO Messages 
                (MessageID, RoomID, AccountID, Content)
                VALUES (:id, :rmid, :sender, :content)";
        $statement = Database::connect()->prepare($sql);
        $statement->execute([
            ":id" => $id,
            ":rmid" => $room,
            ":sender" => $author,
            ":content" => $content
        ]);
        $this->_messages[] = new Message($id, $room, $author, $content);
    }
}
<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 12/1/16
 * Time: 3:53 PM
 */

require_once "Message.php";
require_once "classes/Database.php";
require_once "classes/File.php";

class Chat
{
    public $_messages = [];
    public $_files = [];

    protected $_roomid;

    public function __construct($roomid)
    {
        $this->_roomid = $roomid;
    }

    private function _get_messages($number, $before = -1)
    {

    }

    public function getMessages($number, $before = 99999999999999999999999999)
    {
        $retval = false;
        $sql = "SELECT * 
                FROM Messages
                WHERE RoomID = :rid AND MessageID < :bef
                ORDER BY MessageID DESC
                LIMIT 500";
        $statement = Database::connect()->prepare($sql);
        if ($statement->execute([
            ":rid" => $this->_roomid,
            ":bef" => $before
        ])
        )
        {
            $results = $statement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as $message) {
                $this->_messages[$message["MessageID"]] = new Message($message["MessageID"], $message["RoomID"], $message["AccountID"], $message["Content"], $message["FileID"]);
            }
            $retval = $this->_messages;
        }
        return $retval;
    }

    public function addMessage($id, $room, $author, $content, $fileID = null)
    {
        $sql = "INSERT INTO Messages 
                (MessageID, RoomID, AccountID, Content, FileID)
                VALUES (:id, :rmid, :sender, :content, :fileID)";
        $statement = Database::connect()->prepare($sql);
        $statement->execute([
            ":id" => $id,
            ":rmid" => $room,
            ":sender" => $author,
            ":content" => $content,
            ":fileID" => $fileID
        ]);

        $this->_messages[] = new Message($id, $room, $author, $content, $fileID);
    }

    public function AddFile($id, $path, $name){
        $this->_files[] = new File($id, $path, $name);
    }

    /**
     * @return File[]
     */
    public function getFiles()
    {
        return $this->_files;
    }
}
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

    private function _get_messages($number, $before = -1){

    }

    public function getMessages($number, $before = 99999999999999999999999999){
        $sql = "SELECT * 
                FROM Messages AS m
                  LEFT JOIN files AS f 
                    ON m.FileID = f.FileID
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
            $this->_messages[$message["MessageID"]] = new Message($message["MessageID"], $message["RoomID"], $message["AccountID"], $message["Content"], $message["Filename"]);
        }
    }

    public function addMessage($id, $room, $author, $content, $filepath = null){

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

        if($filepath) {
            $this->addFile($filepath);
        }

        $this->_messages[] = new Message($id, $room, $author, $content);
    }

    public function addFile($filePath) {

        if ($blob = fopen($filePath, 'rb')) {
            $file = new File($filePath);
            $typeID = $file->getTypeID();
            $fileName = basename($filePath);

            $sql = "INSERT INTO Files (Data, Filename, TypeID)
                VALUES(:data, :filename, :typeID)";

            $statement = Database::connect()->prepare($sql);

//        PDO::PARAM_LOB allows for mapping data as stream
            $statement->bindParam(":data", $blob, PDO::PARAM_LOB);
            $statement->bindParam(":filename", $fileName);
            $statement->bindParam("typeID", $typeID);

            if ($statement->execute()) {
                $this->_files[] = $file;
            }
        } else {
            throw new Exception("File couldn't open");
        }
    }
}
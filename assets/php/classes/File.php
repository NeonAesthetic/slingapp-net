<?php

/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 1/11/2017
 * Time: 7:22 PM
 */

require_once "interfaces/DatabaseObject.php";

class File
{
    var $fileID;
    var $filePath;
    var $fileName;
    var $ext;

    public function __construct($fileID, $filePath, $fileName) {
        $this->fileID = $fileID;
        $this->filePath = $filePath;
        $this->fileName = pathinfo($fileName, PATHINFO_FILENAME);
        $this->ext = pathinfo($fileName, PATHINFO_EXTENSION);
    }

    static function Insert($filePath, $fileName) {
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $dbfileName = pathinfo($fileName, PATHINFO_FILENAME);
        $fileID = -1;
        $sql = "INSERT INTO Files (FilePath, Name, Ext)
            VALUES(:filepath, :name, :ext)";

        $statement = Database::connect()->prepare($sql);
        if ($statement->execute([
            ":filepath" => $filePath,
            ":name" => $dbfileName,
            ":ext" => $ext
        ]))
            $fileID = Database::connect()->lastInsertId();

        $json = [];
        $json["Type"] = "File";
        $json["text"] = $fileName;
        $json["filepath"] = $filePath;
        $json["fileid"] = $fileID;

        return json_encode($json);
    }
    /**
     * Function getJSON
     * @param bool $as_array
     * @return array|string
     * This Function allows the return of the encoded JSON object
     * to be used in different areas of the program.
     */
    public function getJSON($as_array = false)
    {
        $json = [];
        $json["Type"] = "File";
        $json["FileName"] = $this->fileName;
        $json["FilePath"] = $this->filePath;
        $json["FileID"] = $this->fileID;
        $json["Ext"] = $this->ext;

        if ($as_array)
            return $json;
        return json_encode($json);
    }

    /**
     * @return string
     */
    public function getExt()
    {
        return $this->ext;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @return string
     */
    public function getFileID() {
        return $this->fileID;
    }
}
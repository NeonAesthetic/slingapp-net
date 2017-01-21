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
    var $typeID;
    var $mime;

    public function __construct($filePath)
    {
        //        echo "basedir: ", getcwd();
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $this->LookupMIME($ext);

        $sql = "SELECT * FROM MimeTypes WHERE MimeType = :mime";

        $statement = Database::connect()->prepare($sql);
        $statement->execute([":mime" => $this->mime]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        $this->typeID = $result['TypeID'];
    }

    private function LookupMIME($ext) {

        switch (strtolower($ext)) {
            case "pdf":
                $this->mime = "pdf";
                break;

            case "tiff":
            case "bmp":
            case "png":
            case "jpeg":
            case "jpg":
                $this->mime = "image";
                break;

            case "264":
            case "3gp":
            case "avi":
            case "mkv":
            case "mov":
            case "avc":
            case "mp4":
                $this->mime = "video";
                break;

            case "tar":
            case "tar.gz":
            case "tar.Z":
            case "tar.bz2":
            case "iso":
            case "bz2":
            case "gz":
            case "lz":
            case "lzma":
            case "lzo":
            case "rz":
            case "7z":
            case "s7z":
            case "dmg":
            case "jar":
            case "rar":
            case "zip":
                $this->mime = "archive";
                break;

            default:
                $this->mime = "text";
        }
    }
    /**
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * @return int
     */
    public function getTypeID()
    {
        return $this->typeID;
    }
}
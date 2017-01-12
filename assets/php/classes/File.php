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
    var $mime;
    var $blob;

    public function __construct($filePath)
    {
        $this->blob = fopen($filePath, 'rb');

//        echo "basedir: ", getcwd();
        $fileName = basename($filePath);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);

        $this->LookupMIME($ext);

        $sql = "SELECT * FROM mimetypes WHERE MimeType = :mime";


        $statement = Database::connect()->prepare($sql);
        $statement->execute([":mime" => $this->mime]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        $sql = "INSERT INTO files (Data, Filename, TypeID)
                VALUES(:data, :filename, :typeID)";

        $statement = Database::connect()->prepare($sql);

//        PDO::PARAM_LOB allows for mapping data as stream
        $statement->bindParam(":data", $this->blob, PDO::PARAM_LOB);
        $statement->bindParam(":filename", $fileName);
        $statement->bindParam("typeID", $result["TypeID"]);

        $statement->execute();
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
}
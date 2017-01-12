<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 1/11/2017
 * Time: 8:21 PM
 * Test Name: Test Downloading/Uploading Files
 * Description: ensures files can be sent and recieved from the database
 */

require_once "classes/File.php";

$file = new File("tests/Files/test.txt");

assert($file->getMime() == "text", "MIME type is text");

//var_dump($file);

function cleanup(){
    try{
        Database::connect()->query("DELETE
                                    FROM Files
                                    WHERE (Filename = 'test.txt')");
    }catch (Exception $e){}
}
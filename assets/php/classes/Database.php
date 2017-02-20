<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/19/16
 * Time: 11:05 AM
 */
class Database
{
    const TESTING = 1;
    const PRODUCTION = 0;
    private static $_instance;

    /**
     * @return PDO
     */
    public static function connect(){
        if(static::$_instance instanceof PDO){
            //pass
        }else{
            static::$_instance = new PDO('mysql:host=127.0.0.1;dbname=sling', "sling", '');
            static::$_instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//            static::$_instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
        }
        return static::$_instance;
    }

    public static function getRandomAnimal(){
        $id = mt_rand(1,32);
        $sql = "SELECT Name FROM Animals WHERE AnimalID = $id;";
        $statement = Database::connect()->prepare($sql);
        if(!$statement->execute()){
            var_dump($statement->errorInfo());
        }
        return $statement->fetch()[0];
    }

    public static function getFlakeID(){
        return str_pad(bindec(decbin(floor(microtime(true) * 1000000))), 20, "1494884", STR_PAD_LEFT);
    }
}
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
        }
        return static::$_instance;
    }

    public static function getRandomAnimal(){
        $sql = "SELECT CONCAT('Anonymous ', Name) FROM Animals ORDER BY RAND() LIMIT 1;";
        $statement = Database::connect()->prepare($sql);
        if(!$statement->execute()){
            var_dump($statement->errorInfo());
        }
        return $statement->fetch()[0];
    }
}
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
            static::$_instance = new PDO('mysql:host=localhost;dbname=sling', "sling", '');
        }
        return static::$_instance;
    }
}
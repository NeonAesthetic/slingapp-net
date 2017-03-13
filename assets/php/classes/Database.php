<?php

/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/19/16
 * Time: 11:05 AM
 */
class Database
{
    /**
     * @var PDO
     */
    private static $_instance;

    /**
     * @return PDO
     */
    public static function connect(){
        $connection_string  =       "mysql:host=" . 
                                    DATABASE_HOST . 
                                    ";dbname=" . 
                                    DATABASE_NAME;

        $username           =       DATABASE_USER;

        $password           =       DATABASE_PASS;


        if (! $username OR ! $password){
            throw new Exception("Database user or password are not set");
        }

        if(static::$_instance instanceof PDO){
            //pass
        }else{
            static::$_instance = new PDO($connection_string, $username, $password);
            static::$_instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return static::$_instance;
    }

    public static function reconnect(){
        static::$_instance = null;
        return static::connect();
    }

    public static function assureConnection(){
        if(! static::$_instance){
            static::connect();
        }
        try{
            static::$_instance->query("SHOW STATUS;")->execute();
        }catch(PDOException $e){
            error_log("DATABASE: Reconnecting");
            static::reconnect();
            return false;
        }
        return true;
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
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


    public function __construct()
    {

    }

    /**
     * @return PDO
     */
    public function connect(){
        return new PDO('mysql:host=localhost;dbname=sling', "sling", '');
    }
}
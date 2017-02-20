<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 11/24/16
 * Time: 12:54 PM
 * Test Name: Random Animal
 */
require_once "classes/Database.php";
mark();
echo Database::getRandomAnimal() . "<br>";
mark("Get random animal");
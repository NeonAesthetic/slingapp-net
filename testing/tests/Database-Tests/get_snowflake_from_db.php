<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 11/27/2016
 * Time: 7:58 PM
 * Test Name: Get Snowflake From DB
 */


require_once "classes/Database.php";

echo Database::connect()->query("SELECT AccountID FROM Accounts LIMIT 1")->fetch()[0];
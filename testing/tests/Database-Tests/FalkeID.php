<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 3/5/2017
 * Time: 12:47 PM
 * Test Name: Snowflake
 */

require_once "classes/Database.php";

echo Database::getFlakeID();
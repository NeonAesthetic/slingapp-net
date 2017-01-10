<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 11/10/16
 * Time: 11:26 AM
 * Test Name: Test Database Connection
 * Description: ensures database can be reached
 */

require_once "classes/Database.php";

var_dump(Database::connect());
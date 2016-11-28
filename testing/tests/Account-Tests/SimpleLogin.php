<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 11/27/2016
 * Time: 8:04 PM
 * Test Name: Simple Login
 */

$t = $_COOKIE["Token"];

require_once "classes/Account.php";

$a = Account::Login($t);

echo $a->getAccountID();
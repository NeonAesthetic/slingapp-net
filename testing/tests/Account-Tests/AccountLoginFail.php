<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 3/12/2017
 * Time: 12:59 AM
 *
 * Test Name: LoginFail
 * Description: Makes sure the account component is behaving correctly
 */
require_once "classes/Account.php";

$account = account::Login("idhchief@gmail.com", "password");
$temp = json_decode($account, true);

var_dump($temp['error']);
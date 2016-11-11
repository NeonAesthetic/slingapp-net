<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 11/6/2016
 * Time: 5:06 PM
 * Test Name: NOINCLUDE
 * Description: Logs into the system assuming there is a valid account
 */

//set_include_path(realpath($_SERVER["DOCUMENT_ROOT"]) . "/assets/php/");
//require_once 'classes/Account.php';
//session_start();
//
//
//if(isset($_POST['login']))
//{
//    $login = new Accounts(0);  //0 used for logging in, 1 for registering
//
//    if($login->isLoggedIn())
//        echo "Success!";
//    else
//        $login->showErrors();
//}
//
//$token = $_SESSION['token'] = md5(uniqid(mt_rand(),true));
//?>

<form action="/assets/php/components/account.php" method="POST">
    <table>
        <tr><td>Email:</td><td><input type="email" name="email" /></td></tr>
        <tr><td>Password:</td><td><input type="password" name="password" /></td></tr>
    </table>
    <p><input type="submit" name="action" value="login" /></p>
</form>


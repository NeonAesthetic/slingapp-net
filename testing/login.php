<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 11/6/2016
 * Time: 10:55 AM
 */

session_start();

$token = $_SESSION['token'] = md5(uniqid(mt_rand(),true));
?>

<form method="POST" action="index.php">
    <table>
        <tr><td>Username:</td><td><input type="text" name="username" /></td></tr>
        <tr><td>Password:</td><td><input type="password" name="password" /></td></tr>
    </table>
    <input type="hidden" name="token" value="<?php echo $token;?>" />
    <input type="submit" name="login" value="login" />
</form>

<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 11/6/2016
 * Time: 5:06 PM
 * Test Name: NOINCLUDE
 * Description: Logs into the system assuming there is a valid account
 */

?>

<form action="/assets/php/components/account.php" method="POST">
    <table>
        <tr><td>Email:</td><td><input type="email" name="email" /></td></tr>
        <tr><td>Password:</td><td><input type="password" name="pass1" /></td></tr>
    </table>
    <p><input type="submit" name="action" value="login" /></p>
</form>


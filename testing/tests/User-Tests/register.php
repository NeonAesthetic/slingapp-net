<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 11/6/2016
 * Time: 5:06 PM
 *
 * Test Name: NOINCLUDE
 * Description: Echos form data
 */
echo "Cookie set: ";
var_dump($_COOKIE);
?>

<form action="/assets/php/components/account.php" method="POST">
    <table>
        <tr><td>FirstName:</td><td><input type="text" name="fname" placeholder="First Name" /></td></tr>
        <tr><td>LastName:</td><td><input type="text" name="lname" placeholder="Last Name"/></td></tr>
        <tr><td>Email:</td><td><input type="email" name="email" placeholder="Email"/></td></tr>
        <tr><td>Password:</td><td><input type="password" name="pass1" placeholder="Password"/></td></tr>
        <tr><td>Confirm Password:</td><td><input type="password" name="pass2" placeholder="Password"/></td></tr>
    </table>
    <p><input type="submit" name="action" value="register" /></p>
</form>

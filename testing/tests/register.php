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

session_start();

set_include_path(realpath($_SERVER["DOCUMENT_ROOT"]) . "/assets/php/");
require_once 'classes/Account.php';

if(isset($_POST['register']))
{
    #include('../../assets/php/classes/Account.php');

    $login = new Accounts(1);

    if($login->process())
        echo "Success!";
    else
        $login->showErrors();
}

$token = $_SESSION['token'] = md5(uniqid(mt_rand(),true));
?>

<form method="POST">
    <table>
        <tr><td>FirstName:</td><td><input type="text" name="fName" placeholder="First Name" /></td></tr>
        <tr><td>LastName:</td><td><input type="text" name="lName" placeholder="Last Name"/></td></tr>
        <tr><td>Email:</td><td><input type="email" name="email" placeholder="Email"/></td></tr>
        <tr><td>Password:</td><td><input type="password" name="password" placeholder="Password"/></td></tr>

    </table>
    <input type="hidden" name="token" value="<?php echo $token;?>" />
    <input type="hidden" name="tokgen" value="<?php echo date('Y-m-d H:i:s:u')?>" />
    <p><input type="submit" name="register" value="Sign Up" /></p>
</form>


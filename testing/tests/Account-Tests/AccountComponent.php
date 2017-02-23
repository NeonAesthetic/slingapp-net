<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 11/11/2016
 * Time: 10:33 AM
 *
 * Test Name: Account Component Tests
 *
 * Description: Runs all the tests required to make sure the account component works as intended: Login, Logout, register, delete account
 */


require_once "classes/Account.php";

/***********************************************************************************************************************
 *          TEST LOGIN METHOD
 **********************************************************************************************************************/
{
    $account = Account::CreateAccount("ozzy.osbourne@gmail.com", "ozzy", "osbourne", "pass");

    if($_SERVER['HTTP_HOST'] == "sling" || $_SERVER['HTTP_HOST'] == "localhost")
        $url = 'http://localhost/assets/php/components/account.php';
    else
        $url = 'https://dev.slingapp.net/assets/php/components/account.php';

    $data = array('action' => 'login', 'email' => 'ozzy.osbourne@gmail.com', 'pass1' => 'pass');

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ),
        'ssl' => array(
            'verify_peer'      => false,
            'verify_peer_name' => false,
        )
    );

    $context  = stream_context_create($options);
    $result = file_get_contents($url, null, $context);

    assert($result == true, "login");

    cleanup();
}

/***********************************************************************************************************************
 *          TEST REGISTER METHOD
 **********************************************************************************************************************/
{
    if($_SERVER['HTTP_HOST'] == "sling" || $_SERVER['HTTP_HOST'] == "localhost")
        $url = 'http://localhost/assets/php/components/account.php';
    else
        $url = 'https://dev.slingapp.net/assets/php/components/account.php';

    $data = array('action' => 'register',
                  'email' => 'ozzy.osbourne@gmail.com',
                  'fname' => 'ozzy', 'lname' => 'osbourne',
                  'pass1' => 'pass', 'pass2' => 'pass'
        );

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ),
        'ssl' => array(
            'verify_peer'      => false,
            'verify_peer_name' => false,
        )
    );

    $context  = stream_context_create($options);
    $result = file_get_contents($url, null, $context);

    assert($result == true, "Register method");

    cleanup();
}

/***********************************************************************************************************************
 *          TEST CREATING TEMP ACCOUNT
 **********************************************************************************************************************/

if($_SERVER['HTTP_HOST'] == "sling" || $_SERVER['HTTP_HOST'] == "localhost")
    $url = 'http://localhost/assets/php/components/account.php';
else
    $url = 'https://dev.slingapp.net/assets/php/components/account.php';

$data = array('action' => 'nocookie');

$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    ),
    'ssl' => array(
        'verify_peer'      => false,
        'verify_peer_name' => false,
    )
);

$context  = stream_context_create($options);
$result = file_get_contents($url, null, $context);

    $account = json_decode($result, true);
    assert($account['LoginToken'] != null, "Created temp account");
cleanup();

function cleanup(){
    try{
        Database::connect()->query("DELETE
                                    FROM Accounts
                                    WHERE (Email = 'ozzy.osbourne@gmail.com')");
    }catch (Exception $e){}
}
<?php
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 11/11/2016
 * Time: 10:33 AM
 * Test Name: All Account Component Tests
 * Description: Runs all the tests required to make sure the account component works as intended: Login, Logout, register, delete account
 */

require_once "classes/Account.php";

/**
 *          TEST LOGIN METHOD
 */
{
    Account::CreateAccount("ozzy.osbourne@gmail.com", "ozzy", "osbourne", "pass");

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
    $result = file_get_contents($url, false, $context);
//    var_dump($result);

    assert($result != null);

    cleanup();
}


/**
 *          TEST LOGOUT METHOD
 */

// setup account first (must be done this way to create session)
//    $url = 'http://localhost/assets/php/components/account.php';
//    $data = array('action' => 'register', 'email' => 'ozzy.osbourne@gmail.com', 'fname' => 'ozzy', 'lname' => 'osbourne', 'pass1' => 'pass', 'pass2' => 'pass');
//
//    $options = array(
//        'http' => array(
//            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
//            'method'  => 'POST',
//            'content' => http_build_query($data)
//        )
//    );
//
//    $context  = stream_context_create($options);
//    $result = file_get_contents($url, false, $context);
//
//var_dump($result);
//
//$data = array('action' => 'logout');
//
//$options = array(
//    'http' => array(
//        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
//        'method'  => 'POST',
//        'content' => http_build_query($data)
//    )
//);

//$context  = stream_context_create($options);
//$result = file_get_contents($url, false, $context);
//if ($result === FALSE) { /* Handle error */ }
//
//var_dump($result);
//
//
//cleanup();

/**
 *          TEST REGISTER METHOD
 */
{
    if($_SERVER['HTTP_HOST'] == "sling" || $_SERVER['HTTP_HOST'] == "localhost")
        $url = 'http://localhost/assets/php/components/account.php';
    else
        $url = 'https://dev.slingapp.net/assets/php/components/account.php';

    $data = array('action' => 'register', 'email' => 'ozzy.osbourne@gmail.com', 'fname' => 'ozzy', 'lname' => 'osbourne', 'pass1' => 'pass', 'pass2' => 'pass');

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

function cleanup(){
    try{
        Database::connect()->query("DELETE
                                    FROM Accounts
                                    WHERE (Email = 'ozzy.osbourne@gmail.com')");
    }catch (Exception $e){}
}
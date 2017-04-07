<?php
/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 4/7/2017
 * Time: 8:34 AM
 */
require_once "classes/Account.php";

function me(){
    $token = $_COOKIE['Token'] OR $_POST['Token'];
    $account = Account::Login($token);

    if(!$account) return new HTTPResponse(["error"=>"Not authenticated"], 405);

    $response_object = [];
    $save_object = false;
    foreach ($_POST as $key=>$value){
        try {
            switch ($key) {
                case "Name": {
                    $account->setScreenName($value);
                    $save_object = true;
                }
                    break;

                case "Email": {
                    $account->_email = $value;
                    $save_object = true;
                }
                    break;

                case 'FirstName':{
                    $account->_fname = $value;
                    $save_object = true;
                }
                    break;

                case 'LastName':{
                    $account->_lname = $value;
                    $save_object = true;
                }
                    break;

                case 'Token':{
                    
                    $account->regenerateToken();

                    setcookie("Token", $account->getToken(), time() + 60 * 60 * 24 * 7, "/");
                    $save_object = true;
                }
                    break;

                case 'Password':{

                   $account->updatePass($value);
                    $save_object = true;
                }
                    break;
            }
        }catch (Exception $e){
            if(!array_key_exists("errors", $response_object)){
                $response_object['errors'] = [];
            }
            $response_object['errors'][] = $e->getMessage();
        }
    }
    if($save_object) $account->update();

    $response_object['account'] = $account->getJSON(true);
    
    return new HTTPResponse($response_object);
}

function create_blank_account(){
    $account = Account::CreateAccount();
    return new HTTPResponse($account->getJSON(true));
}
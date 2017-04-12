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
    $account = false;
    if(!$token) return new HTTPResponse(["error"=>"Not authenticated"], 405);

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
    setcookie("Token", $account->getToken(), time() + 60 * 60 * 24 * 7, "/");
    return new HTTPResponse($account->getJSON(true));
}

function user_view($user_id){
    $sql = "SELECT * FROM Accounts WHERE AccountID = :id";
    $stmt = Database::connect()->prepare($sql);
    $stmt->execute([
        ":id"=>$user_id
    ]);
    $results = $stmt->fetch(PDO::FETCH_ASSOC);
    unset($results['PasswordHash']);
    unset($results['LoginToken']);
    unset($results['LastLogin']);
    unset($results['TokenGenTime']);
    unset($results['AccountActive']);
    unset($results['Email']);
    return new HTTPResponse($results);
}

function authenticate(){
    $response_object = [];
    $status = 200;
    $email = $_POST['Email'];
    $password = $_POST['Password'];
    if( $email AND $password ){
        $account = Account::Login($email, $password);
        if($account){   //login successful
            $response_object = $account->getJSON(true);
            setcookie("Token", $account->getToken(), time() + 60 * 60 * 24 * 7, "/");
        }else{
            $status = 403;
            $response_object['error'] = "Invalid email or password";
        }
    }else{
        $status = 400;
        $response_object['error'] = "Bad request: 'Email' and 'Password' POST values are required";
    }
    return new HTTPResponse($response_object, $status);
}

function register(){
    $response_object = [];
    $status = 200;
    extract($_POST);
    $vars = [
        "Email" => "[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+[.][a-zA-Z]{2}",
        "FirstName" => "[a-zA-Z]{2,30}",
        "LastName" => "[a-zA-Z]{2,30}",
        "Password" => ".{6,30}"

    ];
    foreach ($vars as $value => $pattern){
        $valid = reg_validate($pattern, $$value);
        if(!$valid){
            $status = 400;
            $response_object['error'] = "$value does not pass validation rules";
            $response_object[$value] = $$value;
            $response_object["Valid"] = $valid;
            return new HTTPResponse($response_object, $status);
        }
    }
    if (!Account::CheckDatabase($Email)) {
        $account = Account::CreateAccount($Email, $FirstName, $LastName, $Password, $_COOKIE['Token']);
        if($account){
            $response_object = $account->getJSON(true);
            setcookie("Token", $account->getToken(), time() + 60 * 60 * 24 * 7, "/");
        }else{
            $response_object['error'] = "Account could not be created for an unknown reason";
            $status = 500;
        }
    } else {
        $response_object['error'] = "An account with that email already exists";
        $status = 409;
    }
    return new HTTPResponse($response_object, $status);
}

function reg_validate($regex, $value){
    return preg_match("#".$regex."#", $value);
}





















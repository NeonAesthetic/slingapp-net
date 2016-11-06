<?php

/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 11/6/2016
 * Time: 11:03 AM
 */
class Register
{
    private $email;
    private $password;
    private $passEnc;

    private $errors;
    private $token;

    public function __construct()
    {
        $this->errors = array();
        $this->email = $this->filter($_POST['ruser']);
        $this->password = $this->filter($_POST['rpass']);

        $this->passEnc = crypt($this->password);

    }

    public function process()
    {}

    public function filter($var)    //filter out any unwanted characters in variables
    {
        return preg_replace('/[^a-zA-Z0-9@.]/','',$var);
    }

    public function register()
    {

    }

    public function show_errors()
    {}

    public function valid_data()
    {}

    public function valid_token()
    {}



}
<?php
set_include_path(realpath($_SERVER["DOCUMENT_ROOT"]) . "/assets/php/");
require_once 'classes/Accounts.php';
/**
 * Created by PhpStorm.
 * User: Isaac
 * Date: 11/8/2016
 * Time: 8:48 AM
 */


    $account = Accounts::CreateAccount("asdf@asdf.com", "first", "last", "pass");

    function cleanup()
    {
        $sql = "DELETE FROM Accounts
                WHERE LastName = 'last'";

        $statement = Database::connect()->prepare($sql);
        $statement->execute();
    }
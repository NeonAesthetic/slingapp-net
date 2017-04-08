<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/14/16
 * Time: 8:09 AM
 */

require_once "classes/Account.php";
$token = $_COOKIE['Token'];
$account = Account::Login($token);
if(!$account){
    $account = Account::CreateAccount();
    setcookie("Token", $account->getToken(), time() + 60 * 60 * 24 * 7, "/");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>
        Sling
    </title>

    <link rel="stylesheet" href="/assets/css/semantic.min.css">
    <link rel="stylesheet" href="/assets/css/extra.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">
    <style>
        .contains-image{

            background-size: contain;
            background-position: center center;
            background-repeat: no-repeat;
            position: relative;
        }


    </style>
</head>
<body>
<div class="ui grid">

    <?php
    include "components/navbar.php";
    ?>

</div>
<div class="ui container fluid one item" style="min-height: 80%; width: 800px; display: flex; align-items: center; justify-content: space-around; flex-flow: row wrap; align-content: center" >

    <div class="contains-image" style="background-image: url('slingblock.png'); height: 260px; width: 800px;"></div>

    <div class="" style="width: 98%; font-size: 3em; margin: 20px;text-align: center;"><span>Sharing so easy you'll actually die</span></div>

    <button class="ui button primary huge">Join Room</button><button class="ui button primary huge">Join Room</button>

</div>



</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="/assets/js/semantic.min.js"></script>
<script type='text/javascript' src="/assets/js/sling.js"></script>

<script>
    Account.data = JSON.parse('<?=$account ? $account->getJSON() : '{}'?>');
    window.addEventListener("load", function () {

//        isLoggedIn();
//        getRoomData();


        $('.ui.dropdown')
            .dropdown({
                on:'click'
            })
        ;

        $('.ui.dropdown.login')
            .dropdown({
                on:'click',
                action:'nothing'
            })
        ;

        $('.ui.modal').modal({
            onApprove:function () {
                return false;
            },
            approve:'.positive'
        })

    });

</script>

</html>
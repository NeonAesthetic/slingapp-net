<?php
/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 3/15/17
 * Time: 4:26 PM
 */

?>

<div class="computer tablet mobile only row">
    <div class="ui fixed menu navbar ">

        <a class="ui dropdown item rooms ">Recent Rooms
            <i class="dropdown icon"></i>

            <div class="menu">
                <?php
                if($account){
                    $rooms = $account->getRoomsUserIsIn();
                    foreach ($rooms as $room){
                        $room_name = strlen($room['RoomName']) > 0 ? $room['RoomName'] : "Unnamed Room" ;
                        $num_participants = $room['NumUsers'];
                        ?>
                        <div class="item" onclick="location='/rooms/<?=$room['RoomID']?>'">
                            <h4 style=""><?=$room_name?> </h4>
                            <span data-tooltip="<?=$room['NumUsers']?> user" data-position="right center"><i class="fa fa-user"></i><?=$room['NumUsers']?></span>
                        </div>

                        <?php

                    }
                    if(count($rooms) == 0){
                        ?>
                        <div class="item disabled">
                            <span>No Recent Rooms</span>
                        </div>
                        <?php
                    }
                }else{
                    ?>
                    <div class="item disabled">
                        <span>No Associated Account</span>
                    </div>
                    <?php
                }

                ?>
            </div>
        </a>
        <a class="ui dropdown item rooms " style="padding: 0 10px 0 10px" >
            <i style="padding: 0; margin: 0"><img src="/assets/images/sling64.png" height="24" width="24"></i>

            <div id='sling-context' class="menu" style="min-width: 300px; ">
                <div class="item" onclick="CreateBlankRoom(function(roomid) { location = '/rooms/'+roomid }, function(){alert('Room Could not be created')})">
                    <h4>Create a new Room</h4>
                    <p style="white-space: normal">
                        A Room is an initially private container for messages, files, and
                        audio conversations that happen within it.  Others can be invited
                        to a Room at any time using Invite Codes.
                    </p>
                </div>
            </div>
        </a>
        <?php
        if($account->getEmail()){
            ?>
            <a id="account-dropdown" class="ui dropdown item right">
                <span id='account-label'><?=$account->getName()['First'] . " " . $account->getName()['Last'] . ' (' . $account->getScreenName() . ')' ?></span>
                <i class="dropdown icon"></i>
                <div class="menu">
                    <div class="item" onclick="var name = prompt(); Account.changeName(name)">
                        <i class="fa fa-pencil"></i>
                        Change Screen Name
                    </div>
                    <div class="divider"></div>
                    <div class="item" onclick="logout()">
                        <i class="fa fa-sign-out"></i>
                        Logout
                    </div>
                </div>
            </a>
            <?php
        }else {
            ?>

            <a id='login-dropdown' class="ui dropdown item login right">
                Login
                <i class="dropdown icon"></i>

                <div class="menu">
                    <div class="item">
                        <form id="loginForm" class="ui form" style="min-width: 400px;"
                              onsubmit="submitLogin(); noprop(event)">
                            <div class="field">
                                <label>Email</label>
                                <input type="email" name="email" placeholder="Email" onkeydown="clearError()">
                            </div>

                            <div class="field">
                                <label>Password</label>
                                <input type="password" name="pass1" placeholder="Password" onkeydown="clearError()">
                            </div>
                            <div class="ui two buttons fluid">
                                <button onclick="submitLogin()" type="button" class="ui button primary" style="">Login
                                </button>
                                <div class="or"></div>
                                <button type='button' class="ui button" onclick="$('.ui.modal').modal('show')">Register
                                    instead
                                </button>
                            </div>

                            <div class="ui error message">
                                <p id="loginerror"></p>
                            </div>
                        </form>
                    </div>
                </div>
            </a>
            <?php
        }
        ?>
    </div>
</div>
<div class="ui modal register">
    <div class="header">
        Create an Account
    </div>
    <div class="image content">
        <div class="ui medium image">
            <img src="/assets/images/sling.png">
        </div>
        <form class="ui form fluid" id="registerForm" style="min-width: 400px;">
            <div class="field">
                <input name="fname"  placeholder="first name" type="text" onkeydown="clearError()">
            </div>
            <div class="field">
                <input name="lname"  placeholder="last name" type="text" onkeydown="clearError()">
            </div>
            <div class="field">
                <input name="email"  placeholder="email" type="email" onkeydown="clearError()">
            </div>
            <div class="field">
                <input name="pass1"  placeholder="password" type="password" onkeyup="checkPasswords(this.parentNode.parentNode)">
            </div>
            <div class="field">
                <input name="pass2"  placeholder="confirm password" type="password" onkeyup="checkPasswords(this.parentNode.parentNode)">
            </div>

            <div class="ui error message">
                <p id="registererror"></p>
            </div>
        </form>
    </div>
    <div class="actions">
        <div class="ui black deny button">
            No thanks
        </div>
        <div class="ui positive right labeled icon button" onclick="submitRegister(); return false">
            Submit
            <i class="fa fa-check"></i>
        </div>
    </div>
</div>
<div class="ui modal dialog small">

</div>

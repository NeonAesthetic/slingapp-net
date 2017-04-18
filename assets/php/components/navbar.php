<?php
/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 3/15/17
 * Time: 4:26 PM
 */

?>

<div class="computer tablet mobile only row">
    <div class="ui inverted fixed menu navbar ">
        <a class="ui dropdown item rooms" style="padding: 0 10px 0 10px" >
            <i style="padding: 0; margin: 0; background-image: url('/assets/images/sling_title_w.png'); height: 24px; width: 80px;" class="contains-image">
<!--                <img src="/assets/images/sling_title_w.png" draggable="false" height="24" width="80">-->
            </i>
            <i class="dropdown icon"></i>

            <div id='sling-context' class="menu" style="min-width: 300px; ">
                <div class="item" onclick="Room.showCreateRoomDialog()">
                    <h4>Create a new Room</h4>
                    <p style="white-space: normal">
                        A Room is an initially private container for messages, files, and
                        audio conversations that happen within it.  Others can be invited
                        to a Room at any time using Invite Codes.
                    </p>
                </div>
                <div class="item" onclick="Room.showJoinRoomDialog()">
                    <h4>Join Someone Else's Room</h4>
                    <p style="white-space: normal">
                        If you have an Invite Code, you can use it to join someone else's room.  Click here to input a code.
                    </p>
                </div>
            </div>
        </a>
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
                        <div class="item" onclick="location='/rooms/<?=$room['RoomID']?>'" style="white-space: nowrap; min-width: 200px">
                            <div class="" style="display: inline-block"><?=$room_name?> </div>
                            <div class="ui right floated" style="display: inline-block">
                                <span data-tooltip="<?=$room['NumUsers']?> user(s)" style="color: inherit">
                                    <i class="fa fa-user"></i>
                                    <?=$room['NumUsers']?>
                                </span> &nbsp;
                                <span onclick="API.room" class="ui icon button red basic" data-tooltip="Delete this room" style=" margin: 0; padding: 0.2em 0.3em 0.2em 0.3em;">
                                    <i class="fa fa-times"></i>
                                </span>

                            </div>
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

        <?php
        if($account->isFullAccount()){
            ?>
            <a id="account-dropdown" class="ui dropdown item right">
                <span id='account-label'><?=$account->getName()['First'] . " " . $account->getName()['Last'] . ' (' . $account->getScreenName() . ')' ?></span>
                <i class="dropdown icon"></i>
                <div class="menu">
                    <div class="item" onclick="Account.showChangeNameDialog()">
                        <i class="fa fa-pencil"></i>
                        Change Screen Name
                    </div>
                    <div class="divider"></div>
                    <div class="item" onclick="Account.logout()">
                        <i class="fa fa-sign-out"></i>
                        Sign out
                    </div>
                </div>
            </a>
            <?php
        }else {
            ?>

            <a id='login-dropdown' class="ui dropdown item login right">
                Sign In &nbsp;<span style="color: #888;;"> or </span>&nbsp; Sign up
                <i class="dropdown icon"></i>

                <div class="menu themed">
                    <div class="item" style="background: transparent !important; ">
                        <form id="loginForm" class="ui form" style="min-width: 300px;"
                              onsubmit="submitLogin()">
                            <div class="field">
                                <label>Email</label>
                                <input type="email" name="email" placeholder="Email"  oninput="Form.validate(this, Regex.email)" data-position="left center" data-offset="12">
                            </div>

                            <div class="field">
                                <label>Password</label>
                                <input type="password" name="pass1" placeholder="Password" onchange="Form.validate(this, Regex.password)" data-position="left center" data-offset="12">
                            </div>

                            <div class="ui two buttons fluid">
                                <button onclick="SubmitLoginForm()" type="button" class="ui button primary" style="">
                                    Sign in
                                </button>
                                <div class="or"></div>
                                <button type='button' class="ui button" onclick="$('.ui.register').modal('show')">
                                    Sign up
                                </button>
                            </div>
                            <div class="ui error message">
                                <p id="loginerror">&nbsp;</p>
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
    <div class="ui grid very padded">
        <div class="two column row">
            <div class="column" style="">
                <div class="ui segments">
                    <div class="ui segment">
                        <h4 class="ui header">Signing in gives you benefits:</h4>
                    </div>
                    <div class="ui segment">
                        <p>
                            <ul class="ui list">
                                <li>Rooms you create last forever</li>
                                <li>Never lose track of Rooms you've joined</li>
                                <li>Change your in Room name</li>
                                <li>And More!</li>
                            </ul>
                        </p>
                    </div>
                </div>


            </div>
            <div class="ui column very padded"  style="">
                <form class="ui form" id="registerForm" style="">
                    <div class="ui two fields">
                        <div class="field">
                            <label>First Name</label>
                            <input name="fname"  placeholder="first name" type="text" oninput="Form.validate(this, Regex.name)" data-position="left center" data-offset="12">
                        </div>
                        <div class="field">
                            <label>Last Name</label>
                            <input name="lname"  placeholder="last name" type="text" oninput="Form.validate(this, Regex.name)" data-position="left center" data-offset="12">
                        </div>
                    </div>

                    <div class="field">
                        <label>Email Address</label>
                        <input name="email"  placeholder="email" type="email" oninput="Form.validate(this, Regex.email)" data-position="left center" data-offset="12">
                    </div>
                    <div class="field">
                        <label>Password</label>
                        <input name="pass1"  placeholder="password" type="password" oninput="Form.validate(this, Regex.password)" data-position="left center" data-offset="12">
                    </div>
                    <div class="field">
                        <label>Confirm Password</label>
                        <input name="pass2"  placeholder="confirm password" type="password" oninput="Form.areEqual(this, this.parentNode.parentNode['pass1'])" data-position="left center" data-offset="12">
                    </div>

                    <div class="ui error message">
                        <p id="registererror"></p>
                    </div>
                </form>
            </div>
        </div>



    </div>
    <div class="ui actions">
        <div class="ui positive button" onclick="SubmitRegisterForm(); return false">
            Submit
        </div>
        <div class="ui black deny button">
            No thanks
        </div>

    </div>
</div>
<div id="dialog-box" class="ui tiny modal" style="">
    <div class="content text">
        <h2 class="dialog-title">Title</h2>
        <p class="dialog-content"></p>
        <form class="ui form">
            <input id="dialog-input">
        </form>
    </div>
    <div class="content">
        <div class="actions">
            <div class="ui two buttons">
                <button class="ui green approve button">
                    Submit
                </button>
                <button class="ui black deny button">
                    Cancel
                </button>
            </div>
        </div>
    </div>

</div>

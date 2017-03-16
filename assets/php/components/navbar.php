<?php
/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 3/15/17
 * Time: 4:26 PM
 */

?>
<nav  style="z-index: 999999; position: fixed; top: 0; ">
    <div class="container-fluid">
        <div class="navbar-header">
            <!--        Needs hamburger icon-->
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul style="float: left">
                <li class="drop" style="width: 250px;">
                    <div class="user-avatar"></div><a href="#">Recent Rooms</a> <span aria-hidden="true" class=
                    "glyphicon glyphicon-menu-hamburger orange-txt"></span>
                    <div class="triangle"></div>
                    <div class="dropdownContain">
                        <div class="dropOut">
                            <ul id="RoomsNav" >
                                <li id="NoRooms" <span aria-hidden="true" class="icon-off"></span> No Recent Rooms</li>
                            </ul>
                        </div>
                    </div>
                </li>
            </ul>



            <ul>
                <li id="LoggedOutNavBar" style="float: right">
                    <button id="login-button" class="login-button" onclick="showLogin()"
                            style="margin: 5px;">Login<span id="reg"><br>or sign up</span>
                    </button>
                </li>
                <li id="LoggedInNavBar" class="drop" style="display: none; width: 250px; float: right">
                    <div class="user-avatar"><img src=""></div><a id="NavName" href="#">Stefano</a> <span aria-hidden="true" class=
                    "glyphicon glyphicon-menu-hamburger orange-txt"></span>
                    <div class="triangle"></div>
                    <div class="dropdownContain">
                        <div class="dropOut">
                            <ul>
                                <li onclick="logout()"><span aria-hidden="true" class="icon-off"></span> Log Out</li>
                                <li onclick=""><span aria-hidden="true" class="icon-off"></span> Settings</li>
                            </ul>
                        </div>
                    </div>
                </li>
            </ul>

        </div>

    </div>




</nav>

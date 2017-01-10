<?php

/**
 * Created by PhpStorm.
 * User: Ian Murphy
 * Date: 11/17/2016
 * Time: 10:57 AM
 */
class Components
{
    public static function Navbar(){
        ?>

        <nav class="navbar navbar-fixed-top" >
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand noselect" href="/"><span class="glyphicon glyphicon-blackboard" style="font-size: 24px"> </span>SLING</a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav navbar-left">
                    <li>
                        <div class="btn-group navbar-btn">
                            <button id="context-button" data-toggle="dropdown" class="btn btn-dark dropdown-toggle">Log In <span class="glyphicon glyphicon-log-in"></span></button>
                            <ul id="context-menu" class="dropdown-menu context-menu" style="padding: 5px 0 5px 0; margin-top: 15px;">
                                <li>
                                    Login
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <div class="btn-group navbar-btn">
                            <a class="btn btn-dark dropdown-toggle" href="https://github.com/3jackdaws/Pinboard/issues">Submit a bug</a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>


        <?php

    }
}
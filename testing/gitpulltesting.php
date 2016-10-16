<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 10/16/16
 * Time: 3:35 PM
 */

$output = [];
if(!exec("git pull origin testing", $output)){
    echo "False";
}else{
    foreach ($output as $line){
        echo($line . "<br>");
    }

}

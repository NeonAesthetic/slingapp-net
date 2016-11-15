<?php

function map_test_dir($dir){
    $files = glob($dir);
    //usleep(1000000);
    foreach ($files as $testfile){
        if(!preg_match("/[.]php$/", $testfile)){
            ?>
            <a title="[<?=basename($testfile)?>]" name="folder" data-toggle="collapse" aria-expanded="false" href="#<?=basename($testfile)?>" class='list-group-item list-group-header'>
                <span class="tname"><b><?=basename($testfile)?></b></span>
                <span class='tfile'></span>
                <div class='ico-area'><span class="tcount" onclick="runContainingTests(event, this)"><?=count(glob($testfile . "/*"))?></span></div>
            </a>
            <div class="collapse" id="<?=basename($testfile)?>">
                <?php
                map_test_dir($testfile . "/*");
                ?>
            </div>
            <?php
        }else{
            $text = fread(fopen($testfile, "r"), filesize($testfile));
            preg_match("#(?<=Test Name:) [a-zA-Z0-9 ]+?(?=\n|\r)#", $text, $match);
            $test_name = $match[0];
            if(strpos($test_name, "NOINCLUDE") !== false) continue;
            preg_match("#(?<=Description:).+(?=\n)#", $text, $matches);
            $test_desc = $matches[0];

            ?>
            <a href='#' title="[<?=basename($testfile)?>]: <?=$test_desc?>" name="test" testfile="<?=substr($testfile, 2)?>" onclick='runTest(this)' class='list-group-item'>
                <span class="tname"><?=$test_name?></span>
                <span class='tfile'></span>
                <div class='ico-area'></div>
            </a>
            <?php
        }

    }

}

map_test_dir("./tests/*");
?>
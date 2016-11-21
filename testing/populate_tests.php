<?php

function map_test_dir($dir){
    $files = glob($dir);
    //usleep(1000000);
    foreach ($files as $testfile){
        if(!preg_match("/[.]php$/", $testfile)){
            $num_containing = count(glob($testfile . "/*"));
            ?>
            <a href='#<?=basename($testfile)?>-container' id="<?=basename($testfile)?>-button" title="<?=basename($testfile)?> directory" name="folder" data-toggle="collapse" aria-expanded="false" container="<?=basename($testfile)?>-container" class='list-group-item test-folder' style="">
                <span class="tname"><?=basename($testfile)?></span>
                <span class='tfile'></span>
                <div class="t-btn tcount" title="Contains <?=$num_containing?> objects"><?=$num_containing?></div>
                <div class="t-btn runtest" title="Run all contained tests">RUN</div>
            </a>
            <div class="collapse test-container" id="<?=basename($testfile)?>-container" button="<?=basename($testfile)?>-button" style="border-left: 1px dashed #ddd; padding-left: 8px;">
                <?php
                map_test_dir($testfile . "/*");
                ?>
            </div>
            <?php
        }else{
            $text = fread(fopen($testfile, "r"), filesize($testfile));
            preg_match("#(?<=Test Name:) [a-zA-Z0-9 -]+?(?=\n|\r)#", $text, $match);
            $test_name = $match[0];
            preg_match("#(?<=Type:) [a-zA-Z0-9 ]+?(?=\n|\r)#", $text, $match);
            if($match)
                $test_type = $match[0];
            else    //if no type listed assume test is PHP
                $test_type = "PHP";
            if(strpos($test_name, "NOINCLUDE") !== false) continue;
            preg_match("#(?<=Description:).+(?=\n)#", $text, $matches);
            $test_desc = $matches[0];
            $classname = "list-group-item test ";
            if(strpos($test_type, "JS") != false)
                $classname .= "test-js";
            ?>
            <a href='javascript:;' title="[<?=basename($testfile)?>]: <?=$test_desc?>" name="test" testfile="<?=substr($testfile, 2)?>" class='<?=$classname?>' style=";">
                <span class="tname"><?=$test_name?></span> &middot;
                <span class='tfile'><?=$test_type?></span>
                <div class='ico-area'></div>
            </a>
            <?php
        }

    }

}

map_test_dir("./tests/*");
?>
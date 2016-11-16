<?php

function map_test_dir($dir){
    $files = glob($dir);
    //usleep(1000000);
    foreach ($files as $testfile){
        if(!preg_match("/[.]php$/", $testfile)){
            ?>
            <a title="[<?=basename($testfile)?>]" name="folder" data-toggle="collapse" aria-expanded="false" href="#<?=basename($testfile)?>" class='list-group-item test-folder' style="">
                <span class="tname"><?=basename($testfile)?></span>
                <span class='tfile'></span>

                <div class='ico-area'><span class="tcount" onclick="runContainingTests(event, this)"><?=count(glob($testfile . "/*"))?></span></div>
                <div class="runtest">Run All</div>
            </a>
            <div class="collapse test-container" id="<?=basename($testfile)?>" style="border-left: 1px dashed #ddd; padding-left: 8px;">
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
            <a href='#' title="[<?=basename($testfile)?>]: <?=$test_desc?>" name="test" testfile="<?=substr($testfile, 2)?>" onclick='runTest(this)' class='list-group-item test' style=";">
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
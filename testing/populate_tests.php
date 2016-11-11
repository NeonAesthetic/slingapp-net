<?php
$files = glob("./tests/*");
//usleep(1000000);
foreach ($files as $testfile){
    $text = fread(fopen($testfile, "r"), filesize($testfile));
    preg_match("#(?<=Test Name:) [a-zA-Z0-9 ]+?(?=\n|\r)#", $text, $match);
    $test_name = $match[0];
    if(strpos($test_name, "NOINCLUDE") !== false) continue;
    preg_match("#(?<=Description:).+(?=\n)#", $text, $matches);
    $test_desc = $matches[0];

    echo "<a href='#' title=\"" . $test_desc . "\" name=\"test\" testfile='". basename($testfile) . "' onclick='runTest(this)' class='list-group-item'>" . $test_name . " - <span class='tfile'>" . basename($testfile) . "</span></a>";
}
?>
<?php
header('Content-Type: text/html; charset=utf-8');

require 'vendor/autoload.php';

// arquivo data.csv - 174885 registros - 44 MB
$file = 'data.csv';

//
$start = microtime(true);

$f = fopen($file, 'r');
while ($line = fgets($f)) {
    echo ($line) . '</br>';
}

//
$time_elapsed_secs = microtime(true) - $start;

var_dump($time_elapsed_secs); //float 13.963229179382

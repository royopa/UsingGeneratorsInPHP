<?php
header('Content-Type: text/html; charset=utf-8');

/*
Os Generators fornecem uma maneira fácil e sem clichês para implementar
iterators. Como exemplo, considere implementar a função file():
*/

echo "\n Memory Consumption: ";
echo round(memory_get_usage()/1048576, 2).''.' MB';//Memory Consumption: 0.13 MB

function getLinesFromFile($fileName)
{
    if (!$fileHandle = fopen($fileName, 'r')) {
        return;
    }

    $lines = [];
    while (false !== $line = fgets($fileHandle)) {
        $lines[] = $line;
    }

    fclose($fileHandle);

    return $lines;
}

// arquivo data.csv - 174885 registros - 44 MB
$fileName = 'data.csv';

$lines = getLinesFromFile($fileName);

foreach ($lines as $line) {
    // do something with $line
    echo $line . "\n";
}

echo "\n Memory Consumption: ";
echo round(memory_get_usage()/1048576, 2).''.' MB'; //Memory Consumption: 60.21 MB

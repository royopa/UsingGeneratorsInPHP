<?php
header('Content-Type: text/html; charset=utf-8');

echo "\n Memory Consumption is   ";
echo round(memory_get_usage()/1048576,2).''.' MB'; //Memory Consumption is 0.13 MB

/*
Como você pode ver uma pequena parte de código pode facilmente tornar-se muito
complicada quando transformada em um iterator. Os Generators resolvem este
problema e permite que você implemente iterators de forma muito simples:
*/

function getLinesFromFile($fileName)
{
    $fileHandle = fopen($fileName, 'r');

    if (! $fileHandle) {
        return;
    }

    while (false !== ($line = fgets($fileHandle))) {
        yield $line;
    }

    fclose($fileHandle);
}

// arquivo data.csv - 174885 registros - 44 MB
$fileName = 'data.csv';

$lines = getLinesFromFile($fileName);

foreach ($lines as $line) {
    // do something with $line
    echo '<p>' . $line . '</p>';
}

//Memory Consumption is 0.13 MB
echo "\n Memory Consumption is   ";
echo round(memory_get_usage()/1048576,2).''.' MB';

/*
O código é muito parecido com a implementação baseada em array.
A diferença principal é que ao invés de colocar os valores num array
os valores são "yieldados".

Quando você chamar a função generator pela primeira vez
($lines = getLinesFromFile($fileName)) o argumento é passado, mas nenhum código
é realmente executado. Em vez disso, a função retorna um objeto generator
diretamente. Esse objeto generator implementa a interface Iterator e é
eventualmente percorrida pelo loop foreach.

Sempre que o método Iterator::next() é chamado, o PHP continuará com a execução
da função generator até que ela atinja a expressão yield. O valor dessa
expressão yield é o que a função Iterator::current() retorna.

<?php
header('Content-Type: text/html; charset=utf-8');

echo "\n Memory Consumption is   ";
echo round(memory_get_usage()/1048576,2).''.' MB'; //Memory Consumption is 0.13 MB

/*
A maior desvantagem desse tipo de código é evidente:
O arquivo inteiro será lido em um array gigante.
Dependendo do tamanho do arquivo, o limite de memória pode facilmente ser
atingido. Isso não é o que você normalmente quer. Em vez disso você quer obter
as linhas uma a uma. Os iterators são perfeitos para fazer isso.

Infelizmente implementar iterators requer uma quantidade insana de código
clichê. Ex: Considere esta variante da função anterior usando iterator:
*/

class LineIterator implements Iterator
{
    protected $fileHandle;
    protected $line;
    protected $i;

    public function __construct($fileName)
    {
        if (!$this->fileHandle = fopen($fileName, 'r')) {
            throw new RuntimeException('Couldn\'t open file "' . $fileName . '"');
        }
    }

    public function rewind()
    {
        fseek($this->fileHandle, 0);
        $this->line = fgets($this->fileHandle);
        $this->i = 0;
    }

    public function valid()
    {
        return false !== $this->line;
    }

    public function current()
    {
        return $this->line;
    }

    public function key()
    {
        return $this->i;
    }

    public function next()
    {
        if (false !== $this->line) {
            $this->line = fgets($this->fileHandle);
            $this->i++;
        }
    }

    public function __destruct()
    {
        fclose($this->fileHandle);
    }
}

// arquivo data.csv - 174885 registros - 44 MB
$fileName = 'data.csv';

$lines = new LineIterator($fileName);

foreach ($lines as $line) {
    // do something with $line
    echo '<p>' . $line . '</p>';
}

echo "\n Memory Consumption is   ";
echo round(memory_get_usage()/1048576,2).''.' MB'; //Memory Consumption is 0.13 MB

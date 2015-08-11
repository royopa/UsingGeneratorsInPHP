<?php

namespace Royopa\Generators;

class FileIterator implements Iterator
{
    protected $f;

    public function __construct($file)
    {
        $this->f = fopen($file, 'r');

        if (!$this->f) {
            throw new Exception();
        }
    }
    public function current()
    {
        return fgets($this->f);
    }
    public function key()
    {
        return ftell($this->f);
    }
    public function next()
    {
    }
    public function rewind()
    {
        fseek($this->f, 0);
    }
    public function valid()
    {
        return !feof($this->f);
    }
}

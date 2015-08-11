<?php
header('Content-Type: text/html; charset=utf-8');

/*
Métodos generator em conjunto com a interface IteratorAggregate também podem
ser usados para implementar facilmente as classes traversable:
*/

class Test implements IteratorAggregate
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getIterator()
    {
        foreach ($this->data as $key => $value) {
            yield $key => $value;
        }
        // or whatever other traversation logic the class has
    }
}

$test = new Test(['foo' => 'bar', 'bar' => 'foo']);
foreach ($test as $key => $value) {
    echo $key, ' => ', $value, "\n";
}

/*
Generators também poder ser utilizados de outra forma, como por exemplo ao
invés de produzir valores eles também podem consumí-los. Quando usado dessa
forma eles são frequentemente conhecidos como generators avançados, generators
reversos ou coroutines.

As Coroutines são conceitos bastantes avançados, de modo que é muito difícil de
chegar com exemplos curtos que não sejam artificiais. Para uma introdução
veja um exemplo de como fazer parse de streaming de XMLs usando coroutines.
Se você quiser saber mais, eu recomendo procurar uma apresentação sobre este
assunto.
*/
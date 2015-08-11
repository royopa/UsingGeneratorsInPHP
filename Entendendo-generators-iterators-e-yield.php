<?php
header('Content-Type: text/html; charset=utf-8');

/*
Entendendo generators, iterators e yield

source: https://evenancio.wordpress.com/2013/12/23/entendendo-generators-iterators-e-yield/

Generator é uma espécie de função especial para controlar o comportamento de um
looping numa coleção de itens. A grosso modo, é uma função que retorna uma lista
(array ou vetores), como aquelas funções básicas que as vezes você cria para
alimentar uma coleção de objetos.

A diferença está no comportamento: enquanto no modelo tradicional sua função de
looping adiciona todos os itens numa coleção de uma única vez, e somente quando
esta operação terminar a função retorna esta coleção completamente carregada,
com o uso de generators os itens são entregues para a função chamadora (caller)
um de cada vez através do yield, conforme a coleção for sendo utilizada por
este caller.

Isto garante melhor utilização da memória e incrementa a performance em quase
todas as situações, uma vez que os itens são processados em menor quantidade.
Há cenários a qual os generators não são bem-vindos, como em situações de
recursividade, ou quando é necessário retornar uma outra coleção de itens ou
quando exceções são disparadas dentro do generator – mas estas são situações de
exceção.

Em resumo, os generators lembram muito uma função, mas se comportam como
iterators.

Para exemplificar, segue o modelo tradicional:
*/

function traditionalLooping()
{
    $collection = array();

    for ($i=1; $i<5; $i++) {
        $collection[] = $i;
        echo 'Traditional looping concatenado com ' . $i . "\n";
    }

    return $collection;
}

$result = traditionalLooping();
foreach ($result as $key => $value) {
    echo $value . "\n";
}

/*
A saída do código acima será:

Traditional looping concatenado com 1
Traditional looping concatenado com 2
Traditional looping concatenado com 3
Traditional looping concatenado com 4
1
2
3
4
*/

/*
Isto por que primeiro foi carregado toda a coleção da função "traditionalLooping"
de uma única vez. Depois ela voltou esta coleção para a variável result, onde
foi lido cada um dos resultados e impresso o valor de cada um dos números.

Vamos ver como ficaria o mesmo exemplo com Generator:
*/

echo "\n";

function generatorLooping()
{
    for ($i=1; $i<5; $i++) {
        echo 'Generator looping ' . $i . "\n";
        yield $collection[] = $i;
    }

    echo "\n";
}

$result = generatorLooping();
foreach ($result as $key => $value) {
    echo $value . "\n";
}

/*
Generator looping 1
1
Generator looping 2
2
Generator looping 3
3
Generator looping 4
4
*/

/*
Ou seja, conforme os itens forem solicitados na função caller, eles são
retirados da função Generator, um a um, graças ao YIELD, que guarda a posição
de retirada do item para voltar naquele mesmo ponto, assim que for solicitado
pelo caller.

Este recurso não é novidade desde muito tempo para uma série de linguagens de
programação.
- De fato, ele surgiu entre 1974 e 1975 na linguagem CLU - Barbara Liskov.
- No C#, temos o recurso nativo disponível desde 2005 no .NET Framework 2.0.
- PHP 5.5
- Versões nativas no Python e no Ruby.
- O recurso generators entrou na especificação do ECMAScript 6
- Java não possui o recurso nativamente
*/

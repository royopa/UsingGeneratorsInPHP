<?php
header('Content-Type: text/html; charset=utf-8');

//https://wiki.php.net/rfc/generators#specification

require '../vendor/autoload.php';

/*
#Specification

##Reconhecimento de funções generator

Qualquer função que contém uma declaração yield é automaticamente uma função
generator.

A implementação inicial necessária para as funções generator são marcadas com um modificador asterisco (function*). Esse método tem a vantagem de que os
generators são mais explícitos e também permitem coroutines com menos yield.

A detecção automática foi escolhida em detrimento do modificador asterisco pelas seguintes razões:

Existe uma implementação generator existente no HipHop PHP, que usa a detecção
automática. Usando o modificador asterisco iria quebrar a compatibilidade.
Todas as implementações em outras linguagens (que eu saiba) também usa a
detecção automática. Isso inclui Python, Javascript 1.7 e C#. A única exceção
para isso é o suporte a generator definido pelo ECMAScript Harmony, mas eu sei
que nenhum navegador implementa isso de forma definida.
A sintaxe para "passagem por referência" de yields parece muito feia: function *&gen() yield-less coroutines are a very narrow use case and are also possible with automatic-detection using a code like if (false) yield;.

##Comportamento básico

Quando uma função generator é chamado a execução é suspensa imediatamente
após o bind do parâmetro e um objeto Generator é retornado.

O objeto Generator implementa a interface a seguir:

final class Generator implements Iterator
{
    void  rewind();
    bool  valid();
    mixed current();
    mixed key();
    void  next();

    mixed send(mixed $value);
    mixed throw(Exception $exception);
}
Se o gerador não está ainda em uma declaração de yield (ou seja, apenas foi criado e ainda não foi utilizado como um iterator), então qualquer chamada para rewind, valid, current, key, next ou send retomará o generator até a que
a instrução yield seguinte seja atingida.

Considere esse exemplo:

function gen() {
    echo 'start';
    yield 'middle';
    echo 'end';
}

// A chamada inicial não exibe nenhuma saída
$gen = gen();

// Chamar a função current() retoma o generator, assim "start" é exibido.
// Então a expressão yield é atingida é a string "middle" é retornada.
// então o resultado da função current() é exibido em seguida.
echo $gen->current();

// A execução do generator é retomada novamente, exibindo assim "end"
$gen->next();
A nice side-effect of this behavior is that coroutines do not have to be primed with a next() call before they can be used. (This is required in Python and also the reason why coroutines in Python usually use some kind of decorator that automatically primes the coroutine.)

Apart from the above the Generator methods behave as follows:

rewind: Throws an exception if the generator is currently after the first yield. (More in the “Rewinding a generator” section.)
valid: Returns false if the generator has been closed, true otherwise. (More in the “Closing a generator” section.)
current: Returns whatever was passed to yield or null if nothing was passed or the generator is already closed.
key: Returns the yielded key or, if none was specified, an auto-incrementing key or null if the generator is already closed. (More in the “Yielding keys” section.)
next: Resumes the generator (unless the generator is already closed).
send: Sets the return value of the yield expression and resumes the generator (unless the generator is already closed). (More in the “Sending values” section.)
throw: Throws an exception at the current suspension point in the generator. (More in the “Throwing into the generator” section.)
Yield syntax

The newly introduced yield keyword (T_YIELD) is used both for sending and receiving values inside the generator. There are three basic forms of the yield expression:

yield $key => $value: Yields the value $value with key $key.
yield $value: Yields the value $value with an auto-incrementing integer key.
yield: Yields the value null with an auto-incrementing integer key.
The return value of the yield expression is whatever was sent to the generator using send(). If nothing was sent (e.g. during foreach iteration) null is returned.

To avoid ambiguities the first two yield expression types have to be surrounded by parenthesis when used in expression-context. Some examples when parentheses are necessary and when they aren't:

// these three are statements, so they don't need parenthesis
yield $key => $value;
yield $value;
yield;

// these are expressions, so they require parenthesis
$data = (yield $key => $value);
$data = (yield $value);

// to avoid strange (yield) syntax the parenthesis are not required here
$data = yield;
If yield is used inside a language construct that already has native parentheses, then they don't have to be duplicated:

call(yield $value);
// instead of
call((yield $value));

if (yield $value) { ... }
// instead of
if ((yield $value)) { ... }
The only exception is the array() structure. Not requiring parenthesis would be ambiguous here:

array(yield $key => $value)
// can be either
array((yield $key) => $value)
// or
array((yield $key => $value))
Python also has parentheses requirements for expression-use of yield. The only difference is that Python also requires parentheses for a value-less yield (because the language does not use semicolons).

See also the "Alternative yield syntax considerations" section.

Yielding keys

The languages that currently implement generators don't have support for yielding keys (only values). This though is just a side-effect as these languages don't support keys in iterators in general.

In PHP on the other hand keys are explicitly part of the iteration process and it thus does not make sense to not add key-yielding support. The syntax could be analogous to that of foreach loops and array declarations:

yield $key => $value;
Furthermore generators need to generate keys even if no key was explicitly yielded. In this case it seems reasonable to behave the same as arrays do: Start with the key 0 and always increment by one. If in between an integer key which is larger than the current auto-key is explicitly yielded, then that will be used as the starting point for new auto-keys. All other yielded keys do not affect the auto-key mechanism.

function gen() {
    yield 'a';
    yield 'b';
    yield 'key' => 'c';
    yield 'd';
    yield 10 => 'e';
    yield 'f';
}

foreach (gen() as $key => $value) {
    echo $key, ' => ', $value, "\n";
}

// outputs:
0 => a
1 => b
key => c
2 => d
10 => e
11 => f
This is the same behavior that arrays have (i.e. if gen() instead simply returned an array with the yielded values the keys would be same). The only difference occurs when the generator yield non-integer, but numeric keys. For arrays they are cast, for generators the are not.

##Yield by reference

Generators can also yield by values by reference. To do so the & modifier is added before the function name, just like it is done for return by reference.

This for example allows you to create classes with by-ref iteration behavior (which is something that is completely impossible with normal iterators):

class DataContainer implements IteratorAggregate {
    protected $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function &getIterator() {
        foreach ($this->data as $key => &$value) {
            yield $key => $value;
        }
    }
}
The class can then be iterated using by-ref foreach:

$dataContainer = new DataContainer([1, 2, 3]);
foreach ($dataContainer as &$value) {
    $value *= -1;
}

// $this->data is now [-1, -2, -3]
Only generators specifying the & modifier can be iterated by ref. If you try to iterate a non-ref generator by-ref an E_ERROR is thrown.

##Sending values

Values can be sent into a generator using the send() method. send($value) will set $value as the return value of the current yield expression and resume the generator. When the generator hits another yield expression the yielded value will be the return value of send(). This is just a convenience feature to save an additional call to current().

Values are always sent by-value. The reference modifier & only affects yielded values, not the ones sent back to the coroutine.

A simple example of sending values: Two (interchangeable) logging implementations:

function echoLogger() {
    while (true) {
        echo 'Log: ' . yield . "\n";
    }
}

function fileLogger($fileName) {
    $fileHandle = fopen($fileName, 'a');
    while (true) {
        fwrite($fileHandle, yield . "\n");
    }
}

$logger = echoLogger();
// or
$logger = fileLogger(__DIR__ . '/log');

$logger->send('Foo');
$logger->send('Bar');
Throwing into the generator

Exceptions can be thrown into the generator using the Generator::throw() method. This will throw an exception in the generator's execution context and then resume the generator. It is roughly equivalent to replacing the current yield expression with a throw statement and resuming then. If the generator is already closed the exception will be thrown in the callers context instead (which is equivalent to replacing the throw() call with a throw statement). The throw() method will return the next yielded value (if the exception is caught and no other exception is thrown).

An example of the functionality:

function gen() {
    echo "Foo\n";
    try {
        yield;
    } catch (Exception $e) {
        echo "Exception: {$e->getMessage()}\n";
    }
    echo "Bar\n";
}

$gen = gen();
$gen->rewind();                     // echos "Foo"
$gen->throw(new Exception('Test')); // echos "Exception: Test"
                                    // and "Bar"
Rewinding a generator

Rewinding to some degree goes against the concept of generators, as they are mainly intended as one-time data sources that are not supposed to be iterated another time. On the other hand, most generators probably *are* rewindable and it might make sense to allow it. One could argue though that rewinding a generator is really bad practice (especially if the generator is doing some expensive calculation). Allowing it to rewind would look like it is a cheap operation, just like with arrays. Also rewinding (as in jumping back to the execution context state at the initial call to the generator) can lead to unexpected behavior, e.g. in the following case:

function getSomeStuff(PDOStatement $stmt) {
    foreach ($stmt as $row) {
        yield doSomethingWith($row);
    }
}
Here rewinding would simply result in an empty iterator as the result set is already depleted.

For the above reasons generators will not support rewinding. The rewind method will throw an exception, unless the generator is currently before or at the first yield. This results in the following behavior:

$gen = createSomeGenerator();

// the rewind() call foreach is doing here is okay, because
// the generator is before the first yield
foreach ($gen as $val) { ... }

// the rewind() call of a second foreach loop on the other hand
// throws an exception
foreach ($gen as $val) { ... }
So basically calling rewind is only allowed if it wouldn't do anything (because the generator is already at its initial state). After that an exception is thrown, so accidentally reused generators are easy to find.

##Cloning a generator

Generators cannot be cloned.

Support for cloning was included in the initial version, but removed in PHP 5.5 Beta 3 due to implementational difficulties, unclear semantics and no particularly convincing use cases.

##Closing a generator

When a generator is closed it frees the suspended execution context (as well as all other held variables). After it has been closed valid will return false and both current and key will return null.

A generator can be closed in two ways:

Reaching a return statement (or the end of the function) in a generator or throwing an exception from it (without catching it inside the generator).
Removing all references to the generator object. In this case the generator will be closed as part of the garbage collection process.
If the generator contains (relevant) finally blocks those will be run. If the generator is force-closed (i.e. by removing all references) then it is not allowed to use yield in the finally clause (a fatal error will be thrown). In all other cases yield is allowed in finally blocks.

The following resources are destructed while closing a generator:

The current execution context (execute_data)
Stack arguments for the generator call, and the additional execution context which is used to manage them.
The currently active symbol table (or the compiled variables if no symbol table is in use).
The current $this object.
If the generator is closed during a method call, the object which the method is invoked on (EX(object)).
If the generator is closed during a call, the arguments pushed to the stack.
Any foreach loop variables which are still alive (taken from brk_cont_array).
The current generator key and value
Currently it can happen that temporary variables are not cleaned up properly in edge-case situations. Exceptions are also subject to this problem: https://bugs.php.net/bug.php?id=62210. If that bug could be fixed for exceptions, then it would also be fixed for generators.

##Error conditions

This is a list of generators-related error conditions:

Using yield outside a function: E_COMPILE_ERROR
Using return with a value inside a generator: E_COMPILE_ERROR
Manual construction of Generator class: E_RECOVERABLE_ERROR (analogous to Closure behavior)
Yielding a key that isn't an integer or a key: E_ERROR (this is just a placeholder until Etienne's arbitrary-keys patch lands)
Trying to iterate a non-ref generator by-ref: Exception
Trying to traverse an already closed generator: Exception
Trying to rewind a generator after the first yield: Exception
Yielding a temp/const value by-ref: E_NOTICE (analogous to return behavior)
Yielding a string offset by-ref: E_ERROR (analogous to return behavior)
Yielding a by-val function return value by-ref: E_NOTICE (analogous to return behavior)
This list might not be exhaustive.

##Performance

You can find a small micro benchmark at https://gist.github.com/2975796.
It compares several ways of iterating ranges:

Using generators (xrange)
Using iterators (RangeIterator)
Using arrays implemented in userland (urange)
Using arrays implemented internally (range)
For large ranges generators are consistently faster; about four times faster
than an iterator implementation and even 40% faster than the native range implementation.

For small ranges (around one hundred elements) the variance of the results is
rather high, but from multiple runs it seems that in this case generators are slightly slower than the native implementation, but still faster than the iterator variant.

The tests were run on a Ubuntu VM, so I'm not exactly sure how representative they are.

##Some points from the discussion

Why not just use callback functions?

A question that has come up a few times during discussion: Why not use callback functions, instead of generators? For example the above getLinesFromFile function could be rewritten using a callback:

function processLinesFromFile($fileName, callable $callback) {
    if (!$fileHandle = fopen($fileName, 'r')) {
        return;
    }

    while (false !== $line = fgets($fileHandle)) {
        $callback($line);
    }

    fclose($fileHandle);
}

processLinesFromFile($fileName, function($line) {
    // do something
});
This approach has two main disadvantages:

Firstly, callbacks integrate badly into the existing PHP coding paradigms. Having quadruply-nested closures is something very normal in languages like JavaScript, but rather rare in PHP. Many things in PHP are based on iteration and generators can nicely integrate with this.

A concrete example, which was actually my initial motivation to write the generators patch:

protected function getTests($directory, $fileExtension) {
    $it = new RecursiveDirectoryIterator($directory);
    $it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::LEAVES_ONLY);
    $it = new RegexIterator($it, '(\.' . preg_quote($fileExtension) . '$)');

    $tests = array();
    foreach ($it as $file) {
        // read file
        $fileContents = file_get_contents($file);

        // parse sections
        $parts = array_map('trim', explode('-----', $fileContents));

        // first part is the name
        $name = array_shift($parts);

        // multiple sections possible with always two forming a pair
        foreach (array_chunk($parts, 2) as $chunk) {
            $tests[] = array($name, $chunk[0], $chunk[1]);
        }
    }

    return $tests;
}
This is a function which I use to provide test vectors to PHPUnit. I point it to a directory containing test files and then split up those test files into individual tests + expected output. I can then use the result of the function to feed some test function via @dataProvider.

The problem with the above implementation obviously is that I have to read all tests into memory at once (instead of one-by-one).

How can I solve this problem? By turning it into an iterator obviously! But if you look closer, this isn't actually that easy, because I'm adding new tests in a nested loop. So I would have to implement some kind of complex push-back mechanism to solve the problem. And - getting back on topic - I can't use callbacks here either, because I need a traversable for use with @dataProvider. Generators on the other hand solve this problem very elegantly. Actually, all you have to do to turn it into a lazy generator is replace $tests[] = with yield.

The second, more general problem with callbacks is that it's very hard to manage state across calls. The classic example is a lexer + parser system. If you implement the lexer using a callback (i.e. lex(string $sourceCode, callable $tokenConsumer)) you would have to figure out some way to keep state between subsequent calls. You'd have to build some kind of state machine, which can quickly get really ugly, even for simple problems (just look at the hundreds of states that a typical LALR parser has). Again, generators solve this problem elegantly, because they maintain state implicitly, in the execution state.

##Alternative yield syntax considerations

Andrew proposed to use a function-like syntax for yield instead of the keyword notation. The three yield variants would then look as follows:

yield()
yield($value)
yield($key => $value)
The main advantage of this syntax is that it would avoid the strange parentheses requirements for the yield $value syntax.

One of the main issues with the pseudo-function syntax is that it makes the semantics of yield less clear. Currently the yield syntax looks very similar to the return syntax. Both are very similar in a function, so it is desirable to keep them similar in syntax too.

Generally PHP uses the keyword $expr syntax instead of the keyword($expr) syntax in all places where the statement-use is more common than the expression-use. E.g. include $file; is usually used as a statement and only very rarely as an expression. isset($var) on the other hand is normally used as an expression (a statement use wouldn't make any sense, actually).

As yield will be used as a statement in the vast majority of cases the yield $expr syntax thus seems more appropriate. Furthermore the most common expression-use of yield is value-less, in which case the parentheses requirements don't apply (i.e. you can write just $data = yield;).

So the function-like yield($value) syntax would optimize a very rare use case (namely $recv = yield($send);), at the same time making the common use cases less clear.

##Patch

The current implementation can be found in this branch: https://github.com/nikic/php-src/tree/addGeneratorsSupport.

I also created a PR so that the diff can be viewed more easily: https://github.com/php/php-src/pull/177

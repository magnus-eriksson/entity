<?php

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/Entity001.php';
require __DIR__ . '/Entity002.php';
require __DIR__ . '/Entity003.php';
require __DIR__ . '/Entity004.php';
require __DIR__ . '/Entity005.php';

$objects = [];

$iterations = 15000;
$start      = microtime(true);

for ($i = 0; $i < $iterations; $i++) {
    $objects[] = new Entity001([
        'integer'   => $i,
        'string'    => 'test' . $i,
        'bool'      => true,
        'array'     => ['foo' . $i],
        'any'       => 'something' . $i,
    ]);

    $x = 100000 + $i;
    $objects[] = new Entity002([
        'integer'   => $x,
        'string'    => 'test' . $x,
        'bool'      => true,
        'array'     => ['foo' . $x],
        'any'       => 'something' . $x,
    ]);

    $x = 200000 + $i;
    $objects[] = new Entity003([
        'integer'   => $x,
        'string'    => 'test' . $x,
        'bool'      => true,
        'array'     => ['foo' . $x],
        'any'       => 'something' . $x,
    ]);

    $x = 300000 + $i;
    $objects[] = new Entity004([
        'integer'   => $x,
        'string'    => 'test' . $x,
        'bool'      => true,
        'array'     => ['foo' . $x],
        'any'       => 'something' . $x,
    ]);

    $x = 400000 + $i;
    $objects[] = new Entity005([
        'integer'   => $x,
        'string'    => 'test' . $x,
        'bool'      => true,
        'array'     => ['foo' . $x],
        'any'       => 'something' . $x,
    ]);
}

$end     = microtime(true);
$peak    = memory_get_peak_usage() / 1024;
$time    = $end - $start;

echo 'Entities: ' . count($objects) . PHP_EOL;
echo 'Time: ' . round($time, 6) . ' sec', PHP_EOL;
echo round($peak, 2) . ' kB' . PHP_EOL;

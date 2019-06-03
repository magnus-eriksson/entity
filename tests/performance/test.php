<?php

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/Entity001.php';
require __DIR__ . '/Entity002.php';
require __DIR__ . '/Entity003.php';
require __DIR__ . '/Entity004.php';
require __DIR__ . '/Entity005.php';

$objects = [];

function humanSize($bytes)
{
    $decimals = 2;
    $size     = ['B','kB','MB','GB','TB','PB','EB','ZB','YB'];
    $factor   = floor((strlen($bytes) - 1) / 3);

    return sprintf("%.{$decimals}f"
        , $bytes / pow(1024, $factor))
        . ' ' . @$size[$factor];
}

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
$peak    = memory_get_peak_usage();
$time    = $end - $start;

echo 'Unique entities: 5' . PHP_EOL;
echo 'Instances created per entity: ' . $iterations . PHP_EOL;
echo 'Total entities created: ' . count($objects) . PHP_EOL;
echo 'Total time: ' . round($time, 6) . ' sec', PHP_EOL;
echo 'Total memory: ' . humanSize($peak) . PHP_EOL;

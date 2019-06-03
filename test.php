<?php

require __DIR__ . '/vendor/autoload.php';

$objects = [];

$iterations = 15000;
$start      = microtime(true);

for ($i = 0; $i < $iterations; $i++) {
    $objects[] = new Tests\Entities\Types([
        'integer'   => $i,
        'string'    => 'test' . $i,
        'bool'      => true,
        'array'     => ['foo' . $i],
        'any'       => 'something' . $i,
    ]);
}

$end     = microtime(true);
$peak    = memory_get_peak_usage() / 1024;
$time    = $end - $start;

echo 'Entities: ' . count($objects) . PHP_EOL;
echo 'Time: ' . round($time, 6) . ' sec', PHP_EOL;
echo round($peak, 2) . ' kB' . PHP_EOL;

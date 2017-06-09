<?php

$composer = require_once __DIR__ . '/../../vendor/autoload.php';
$composer->setUseIncludePath(true);

use Graze\BufferedConsole\BufferedConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutput;

$output = new ConsoleOutput();
$buffer = new BufferedConsoleOutput($output);

$lines = [
    '<info>first</info> ',
    '<error>second</error> ',
    'third ',
    'fourth ',
    'fifth ',
];

$buffer->reWrite($lines, true);

for ($i = 0; $i < 500; $i++) {
    usleep(5000);
    $lines = array_map(function ($str) use ($i) {
        return $str . (rand(1, 10) > 5 ? '█' : '');
    }, $lines);
    $buffer->reWrite($lines, true);
}

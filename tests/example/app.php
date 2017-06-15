<?php
/**
 * This file is part of graze/console-diff-renderer.
 *
 * Copyright (c) 2017 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license https://github.com/graze/console-diff-renderer/blob/master/LICENSE.md
 * @link    https://github.com/graze/console-diff-renderer
 */

$composer = require_once __DIR__ . '/../../vendor/autoload.php';
$composer->setUseIncludePath(true);

use Graze\DiffRenderer\DiffConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutput;

$output = new ConsoleOutput();
$buffer = new DiffConsoleOutput($output);

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

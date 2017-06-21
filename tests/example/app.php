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
    ['title' => 'first', 'padding' => 0],
    ['title' => 'second', 'padding' => 0],
    ['title' => 'third', 'padding' => 0],
    ['title' => 'fourth', 'padding' => 0],
    ['title' => 'fifth', 'padding' => 0],
];

$format = function ($arr) {
    switch (rand(1, 10)) {
        case 1:
        case 2:
        case 3:
            $title = '<info>' . $arr['title'] . '</info>';
            break;
        case 4:
        case 5:
        case 6:
            $title = '<error>' . $arr['title'] . '</error>';
            break;
        default:
            $title = $arr['title'];
            break;
    }
    return $title . ' ' . str_repeat('█', $arr['padding']);
};
$out = array_map($format, $lines);
$buffer->reWrite($out, true);

for ($i = 0; $i < 500; $i++) {
    usleep(10000);
    $lines = array_map(function ($arr) {
        if (rand(1, 10) < 5) {
            $arr['padding']++;
        }
        return $arr;
    }, $lines);
    $out = array_map($format, $lines);
    $buffer->reWrite($out, true);
}

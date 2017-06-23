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

namespace Graze\DiffRenderer\Test\Unit\Diff;

use Graze\DiffRenderer\Diff\ConsoleDiff;
use Graze\DiffRenderer\Test\TestCase;

class ConsoleDiffTest extends TestCase
{
    /**
     * @dataProvider differenceWithANSIData
     *
     * @param string[] $old
     * @param string[] $new
     * @param string[] $expected
     */
    public function testDifferenceWithANSI(array $old, array $new, array $expected)
    {
        $diff = new ConsoleDiff();

        $this->assertEquals($expected, $diff->lines($old, $new));
    }

    /**
     * @return array
     */
    public function differenceWithANSIData()
    {
        return [
            [ // col now equals the strip_tags version (hence 6)
                ["\e[32mfirst\e[39m", "\e[32msecond\e[39m"],
                ["\e[32mnew\e[39m", "\e[32msecond cake\e[39m"],
                [
                    ["col" => 0, "str" => "\e[32mnew\e[39m"],
                    ["col" => 6, "str" => "\e[32m cake\e[39m"],
                ],
            ],
            [ // multiple tags should represent all tags
                ["\e[32m\e[37;41mfirst\e[39;49m\e[39m", "\e[32msecond\e[39m"],
                ["\e[32m\e[37;41mnew\e[39;49m\e[39m", "\e[32msecond cake\e[39m"],
                [
                    ["col" => 0, "str" => "\e[32m\e[37;41mnew\e[39;49m\e[39m"],
                    ["col" => 6, "str" => "\e[32m cake\e[39m"],
                ],
            ],
            [
                ["\e[32m\e[37;41mfirst\e[39;49m\e[39m", "\e[32msecond\e[39m"],
                ["\e[32m\e[37;41mnew\e[39;49m\e[39m", "\e[32msecond cake\e[39m"],
                [
                    ["col" => 0, "str" => "\e[32m\e[37;41mnew\e[39;49m\e[39m"],
                    ["col" => 6, "str" => "\e[32m cake\e[39m"],
                ],
            ],
            [ // col now equals the strip_tags version (hence 6)
                ["\e[32mfirst\e[39m", "\e[32msecond\e[39m"],
                ["\e[37;41mnew\e[39m", "\e[32msecond cake\e[39m"],
                [
                    ["col" => 0, "str" => "\e[37;41mnew\e[39m"],
                    ["col" => 6, "str" => "\e[32m cake\e[39m"],
                ],
            ],
        ];
    }

    /**
     * @dataProvider firstDifferenceData
     *
     * @param string $left
     * @param string $right
     * @param int    $pos
     */
    public function testFirstDifference($left, $right, $pos)
    {
        $diff = new ConsoleDiff();
        $this->assertEquals($pos, $diff->firstDifference($left, $right));
    }

    /**
     * @return array
     */
    public function firstDifferenceData()
    {
        return [
            ['abcdef', 'abcdef', -1],
            ['abcdef', 'abcde', 5],
            ['abcdef', 'bcdef', 0],
            ["\e[32mfish", "\e[32mnew", 0],
            ["new\e[32mfish", "new\e[32mcake", 3],
            ["\e[32mfish", "\e[32;49mfish", 0],
            ["new\e[32mcash", "nes\e[32mcash", 2]
        ];
    }
}

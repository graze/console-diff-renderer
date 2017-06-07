<?php

namespace Graze\BufferedConsole\Test\Unit\Diff;

use Graze\BufferedConsole\Diff\FirstDiff;
use Graze\BufferedConsole\Test\TestCase;

class FirstDiffTest extends TestCase
{
    /**
     * @dataProvider firstDifferenceData
     *
     * @param string $left
     * @param string $right
     * @param int    $pos
     */
    public function testFirstDifference($left, $right, $pos)
    {
        $diff = new FirstDiff();
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
            ['', 'abcdef', 0],
            ['abcdef', '', 0],
            ['<complex> other things </complex>', '<complex> other thingies </complex>', 21],
        ];
    }

    /**
     * @dataProvider lineDifferenceData
     *
     * @param string[] $old
     * @param string[] $new
     * @param array    $expected
     */
    public function testLineDifference(array $old, array $new, array $expected)
    {
        $diff = new FirstDiff();
        $this->assertEquals($expected, $diff->lines($old, $new));
    }

    /**
     * @return array
     */
    public function lineDifferenceData()
    {
        return [
            [
                ['first', 'second'],
                ['first', 'second', 'third'],
                [
                    null,
                    null,
                    ['col' => 0, 'str' => 'third'],
                ],
            ],
            [
                ['first', 'second'],
                ['first'],
                [
                    null,
                    ['col' => 0, 'str' => ''],
                ],
            ],
            [
                ['first', 'second'],
                ['first mod', 'secmod'],
                [
                    ['col' => 5, 'str' => ' mod'],
                    ['col' => 3, 'str' => 'mod'],
                ],
            ],
            [
                ['first bits', 'second bits'],
                ['first thing bits', 'second thing bits'],
                [
                    ['col' => 6, 'str' => 'thing bits'],
                    ['col' => 7, 'str' => 'thing bits'],
                ],
            ],
        ];
    }
}

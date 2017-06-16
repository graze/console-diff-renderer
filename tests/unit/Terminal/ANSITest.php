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

namespace Graze\DiffRenderer\Test\Unit\Terminal\ANSI;

use Graze\DiffRenderer\Terminal\ANSI;
use Graze\DiffRenderer\Terminal\CursorInterface;
use Graze\DiffRenderer\Test\TestCase;

class ANSITest extends TestCase
{
    /** @var ANSI */
    private $cursor;

    public function setUp()
    {
        parent::setUp();

        $this->cursor = new ANSI();
    }

    public function testImplements()
    {
        $this->assertInstanceOf(CursorInterface::class, $this->cursor);
    }

    public function testMove()
    {
        $this->assertEquals("\e[12;13H", $this->cursor->move(12, 13));
        $this->assertEquals("\e[2;8H", $this->cursor->move(2, 8));
    }

    public function testMoveUp()
    {
        $this->assertEquals("\e[1A", $this->cursor->moveUp(1));
        $this->assertEquals("\e[8A", $this->cursor->moveUp(8));
    }

    public function testMoveDown()
    {
        $this->assertEquals("\e[2B", $this->cursor->moveDown(2));
        $this->assertEquals("\e[7B", $this->cursor->moveDown(7));
    }

    public function testMoveLeft()
    {
        $this->assertEquals("\e[3D", $this->cursor->moveLeft(3));
        $this->assertEquals("\e[12D", $this->cursor->moveLeft(12));
    }

    public function testMoveRight()
    {
        $this->assertEquals("\e[6C", $this->cursor->moveRight(6));
        $this->assertEquals("\e[13C", $this->cursor->moveRight(13));
    }

    public function testEraseToEnd()
    {
        $this->assertEquals("\e[K", $this->cursor->eraseToEnd());
    }

    public function testEraseToStart()
    {
        $this->assertEquals("\e[1K", $this->cursor->eraseToStart());
    }

    public function testEraseDown()
    {
        $this->assertEquals("\e[J", $this->cursor->eraseDown());
    }

    public function testEraseUp()
    {
        $this->assertEquals("\e[1J", $this->cursor->eraseUp());
    }

    public function testEraseScreen()
    {
        $this->assertEquals("\e[2J", $this->cursor->eraseScreen());
    }

    /**
     * @dataProvider filterData
     *
     * @param string $string
     * @param string $expected
     */
    public function testFilter($string, $expected)
    {
        $this->assertEquals($expected, $this->cursor->filter($string));
    }

    /**
     * @return array
     */
    public function filterData()
    {
        return [
            ["bla\e[12;13Hbla", 'blabla'],
            ["bla\e[1Abla", 'blabla'],
            ["bla\e[2Bbla", 'blabla'],
            ["bla\e[3Dbla", 'blabla'],
            ["bla\e[6Cbla", 'blabla'],
            ["bla\e[Kbla", 'blabla'],
            ["bla\e[1Kbla", 'blabla'],
            ["bla\e[Jbla", 'blabla'],
            ["bla\e[1Jbla", 'blabla'],
            ["bla\e[2Jbla", 'blabla'],
            ["bla\nbla", 'blabla'],
            ["bla\rbla", 'blabla'],
            [
                "bla\e[12;13H" .
                "bla\e[1A" .
                "bla\e[2B" .
                "bla",
                'blablablabla',
            ],
        ];
    }
}

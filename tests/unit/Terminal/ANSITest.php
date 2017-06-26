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

    public function testShowCursor()
    {
        $this->assertEquals("\e[?25h", $this->cursor->showCursor());
    }

    public function testHideCursor()
    {
        $this->assertEquals("\e[?25l", $this->cursor->hideCursor());
    }

    /**
     * @dataProvider filterData
     *
     * @param string $string
     * @param string $replacement
     * @param string $expected
     */
    public function testFilter($string, $replacement, $expected)
    {
        $this->assertEquals($expected, $this->cursor->filter($string, $replacement));
    }

    /**
     * @return array
     */
    public function filterData()
    {
        return [
            ["bla\e[12;13Hbla", '', 'blabla'],
            ["bla\e[1Abla", '', 'blabla'],
            ["bla\e[2Bbla", '', 'blabla'],
            ["bla\e[3Dbla", '', 'blabla'],
            ["bla\e[6Cbla", '', 'blabla'],
            ["bla\e[Kbla", '', 'blabla'],
            ["bla\e[1Kbla", '', 'blabla'],
            ["bla\e[Jbla", '', 'blabla'],
            ["bla\e[1Jbla", '', 'blabla'],
            ["bla\e[2Jbla", '', 'blabla'],
            ["bla\rbla", '', 'blabla'],
            [
                "bla\e[12;13H" .
                "bla\e[1A" .
                "bla\e[2B" .
                "bla\e[12;33;54m",
                '',
                'blablablabla',
            ],
            ["bla\e(bla\e)bla\eI", '', "blablabla\eI"],
            ["bla\e[1Abla", '<>', 'bla<><><><>bla'], // applies the replacement once per character being replaced
            ["bla\e[1A1bla", '', 'bla1bla'],
            ["bla\e[1A2bla", '', 'bla2bla'],
            ["bla\e[1A3bla", '', 'bla3bla'],
            ["bla\e[1A4bla", '', 'bla4bla'],
            ["bla\e[1A5bla", '', 'bla5bla'],
            ["bla\e[1A6bla", '', 'bla6bla'],
            ["bla\e[1A7bla", '', 'bla7bla'],
            ["bla\e[1A8bla", '', 'bla8bla'],
            ["bla\e[1A9bla", '', 'bla9bla'],
            ["bla\e[1A0bla", '', 'bla0bla'],
            [
                "\e[13m1234567890\e[42m1234567890\e[15m12345678901234567890\e[42m",
                '',
                "1234567890123456789012345678901234567890",
            ],
        ];
    }

    /**
     * @dataProvider getCurrentFormattingData
     *
     * @param string $string
     * @param string $expected
     */
    public function testGetCurrentFormatting($string, $expected)
    {
        $actual = $this->cursor->getCurrentFormatting($string);
        $this->assertEquals(bin2hex($expected), bin2hex($actual));
    }

    /**
     * return array
     */
    public function getCurrentFormattingData()
    {
        return [
            ["bla\e[4mcake", "\e[4m"],
            ["bla\e[4;3mcake\e[2mfish", "\e[4;3;2m"],
            ["bla\e[4;0;3mcake", "\e[3m"],
            ["bla\e[38;2;123;123;123;5;2mcake", "\e[38;2;123;123;123;5;2m"],
            ["bla\e[32;43mcake\e[39mfish", "\e[43m"],
            ["bla\e[32mcake\e[39m", ''],
            ["bla\e[32,42mcake\e[mboo", ''],
            ["bla\e[1;2;3;4;5mcake\e[22m", "\e[3;4;5m"],
            ["bla\e[32;43mcake\e[38;5;123mboo\e[49m", "\e[32;38;5;123m"] // this could be \e[38;5;123m but still works
        ];
    }
}

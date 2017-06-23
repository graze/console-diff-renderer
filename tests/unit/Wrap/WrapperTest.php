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

namespace Graze\DiffRenderer\Test\Unit\Wrap;

use Graze\DiffRenderer\Terminal\DimensionsInterface;
use Graze\DiffRenderer\Terminal\Terminal;
use Graze\DiffRenderer\Test\TestCase;
use Graze\DiffRenderer\Wrap\Wrapper;
use Mockery;

class WrapperTest extends TestCase
{
    public function setUp()
    {
        mb_internal_encoding("UTF-8");
    }

    public function testWrapper()
    {
        $dimensions = Mockery::mock(DimensionsInterface::class);
        $dimensions->shouldReceive('getWidth')
                   ->andReturn(10, 20);
        $terminal = new Terminal(null, $dimensions);
        $wrapper = new Wrapper($terminal);

        $this->assertEquals(10, $wrapper->getWidth());
        $this->assertEquals(20, $wrapper->getWidth());
    }

    /**
     * @dataProvider wrapData
     *
     * @param string|string[] $input
     * @param int             $width
     * @param string[]        $expected
     */
    public function testWrap($input, $width, array $expected)
    {
        $dimensions = Mockery::mock(DimensionsInterface::class);
        $dimensions->shouldReceive('getWidth')
                   ->andReturn($width);
        $terminal = new Terminal(null, $dimensions);
        $wrapper = new Wrapper($terminal);

        $this->assertEquals($expected, $wrapper->wrap($input));
    }

    /**
     * @return array
     */
    public function wrapData()
    {
        return [
            [ // simple string
                ['1234567890'],
                5,
                [
                    '12345',
                    '67890',
                ],
            ],
            [ // boundary test
                ['123456'],
                5,
                ['12345', '6'],
            ],
            [ // array of strings
                [
                    '1234567890123456789012345678901234567890',
                    '123456789012345678901234567890',
                ],
                20,
                [
                    '12345678901234567890',
                    '12345678901234567890',
                    '12345678901234567890',
                    '1234567890',
                ],
            ],
            [ // support multi-byte characters
                ['😀😃😄😁😆😅😂🤣☺😊😇🙂🙃😉😌😍😘😗😙😚😋😜😝😛🤑🤗🤓😎🤡🤠😏😒😞😔😟😕🙁😣😖😫😩😤😠😡😶😐😑😯😦😧😮😲😵😳😱😨😰😢😥'],
                20,
                [
                    '😀😃😄😁😆😅😂🤣☺😊😇🙂🙃😉😌😍😘😗😙😚',
                    '😋😜😝😛🤑🤗🤓😎🤡🤠😏😒😞😔😟😕🙁😣😖😫',
                    '😩😤😠😡😶😐😑😯😦😧😮😲😵😳😱😨😰😢😥',
                ],
            ],
            [ // strip tags and wrap on non stripped version
                [
                    "\e[13m1234567890\e[42m1234567890\e[15m12345678901234567890\e[42m",
                    "\e[17m12345678901234567890\e[42m1234567890",
                ],
                20,
                [
                    "\e[13m1234567890\e[42m1234567890\e[15m",
                    "12345678901234567890\e[42m",
                    "\e[17m12345678901234567890\e[42m",
                    '1234567890',
                ],
            ],
            [ // ignore tags
                ['<info>info</info>infowarning<warning>warning</warning>warning'],
                10,
                [
                    '<info>info',
                    '</info>inf',
                    'owarning<w',
                    'arning>war',
                    'ning</warn',
                    'ing>warnin',
                    'g'
                ],
            ],
        ];
    }

    /**
     * @dataProvider trimData
     *
     * @param string|string[] $input
     * @param int             $width
     * @param array           $expected
     */
    public function testTrim($input, $width, array $expected)
    {
        $dimensions = Mockery::mock(DimensionsInterface::class);
        $dimensions->shouldReceive('getWidth')
                   ->andReturn($width);
        $terminal = new Terminal(null, $dimensions);
        $wrapper = new Wrapper($terminal);

        $this->assertEquals($expected, $wrapper->trim($input));
    }

    /**
     * @return array
     */
    public function trimData()
    {
        return [
            [ // simple string
                ['1234567890'],
                5,
                ['12345'],
            ],
            [ // boundary test
                ['123456'],
                5,
                ['12345'],
            ],
            [
                ["\x20\x20\x20\x20\x20"],
                3,
                ["\x20\x20\x20"],
            ],
            [ // array of strings
                [
                    '1234567890123456789012345678901234567890',
                    '123456789012345678901234567890',
                ],
                20,
                [
                    '12345678901234567890',
                    '12345678901234567890',
                ],
            ],
            [ // support multi-byte characters
                ['😀😃😄😁😆😅😂🤣☺😊😇🙂🙃😉😌😍😘😗😙😚😋😜😝😛🤑🤗🤓😎🤡🤠😏😒😞😔😟😕🙁😣😖😫😩😤😠😡😶😐😑😯😦😧😮😲😵😳😱😨😰😢😥'],
                20,
                ['😀😃😄😁😆😅😂🤣☺😊😇🙂🙃😉😌😍😘😗😙😚'],
            ],
            [ // strip tags and trim on non stripped version
                [
                    "\e[13m1234567890\e[42m1234567890\e[15m12345678901234567890\e[42m",
                    "\e[17m12345678901234567890\e[42m1234567890",
                ],
                20,
                [
                    "\e[13m1234567890\e[42m1234567890\e[15m",
                    "\e[17m12345678901234567890\e[42m",
                ],
            ],
            [ // ignore tags
                ['<info>info</info>infowarning<warning>warning</warning>warning'],
                18,
                ['<info>info</info>i'],
            ],
        ];
    }
}

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

namespace Graze\DiffRenderer\Test\Unit\Terminal;

use Graze\DiffRenderer\Terminal\CursorInterface;
use Graze\DiffRenderer\Terminal\DimensionsInterface;
use Graze\DiffRenderer\Terminal\Terminal;
use Graze\DiffRenderer\Test\TestCase;
use Mockery;
use ReflectionClass;

class TerminalTest extends TestCase
{
    public function testTerminalUsesTerminalToGetTerminalSize()
    {
        $dimensions = Mockery::mock(DimensionsInterface::class);
        $dimensions->shouldReceive('getWidth')
                   ->andReturn(70);
        $dimensions->shouldReceive('getHeight')
                   ->andReturn(40);

        $terminal = new Terminal(null, $dimensions);

        $this->assertEquals(70, $terminal->getWidth());
        $this->assertEquals(40, $terminal->getHeight());
    }

    public function testTerminalPassesThroughAllCursorMethods()
    {
        $cursor = Mockery::mock(CursorInterface::class);
        $terminal = new Terminal($cursor);

        $reflection = new ReflectionClass(CursorInterface::class);
        $methods = $reflection->getMethods();

        foreach ($methods as $method) {
            if ($method->getNumberOfParameters() > 0) {
                $args = array_map(function () {
                    return rand(0, 100);
                }, $method->getParameters());
                $cursor->shouldReceive($method->getName())
                       ->withArgs($args)
                       ->andReturn($method->getName());
                $this->assertEquals($method->getName(), call_user_func_array([$terminal, $method->getName()], $args));
            } else {
                $cursor->shouldReceive($method->getName())
                       ->andReturn($method->getName());
                $this->assertEquals($method->getName(), call_user_func([$terminal, $method->getName()]));
            }
        }
    }
}

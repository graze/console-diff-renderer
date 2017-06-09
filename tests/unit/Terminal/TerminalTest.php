<?php
/**
 * This file is part of graze/buffered-console.
 *
 * Copyright (c) 2017 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license https://github.com/graze/buffered-console/blob/master/LICENSE.md
 * @link    https://github.com/graze/buffered-console
 */

namespace Graze\BufferedConsole\Test\Unit\Terminal;

use Graze\BufferedConsole\Terminal\CursorInterface;
use Graze\BufferedConsole\Terminal\Terminal;
use Graze\BufferedConsole\Test\TestCase;
use Mockery;
use ReflectionClass;
use ReflectionParameter;
use Symfony\Component\Console\Terminal as SymfonyTerminal;

class TerminalTest extends TestCase
{
    public function testTerminalUsesTerminalToGetTerminalSize()
    {
        $symfonyTerminal = Mockery::mock(SymfonyTerminal::class);
        $symfonyTerminal->shouldReceive('getWidth')
                        ->andReturn(70);
        $symfonyTerminal->shouldReceive('getHeight')
                        ->andReturn(40);

        $terminal = new Terminal(null, $symfonyTerminal);

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

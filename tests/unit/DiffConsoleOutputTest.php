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

namespace Graze\DiffRenderer\Test\Unit;

use Graze\DiffRenderer\DiffConsoleOutput;
use Graze\DiffRenderer\Terminal\DimensionsInterface;
use Graze\DiffRenderer\Terminal\Terminal;
use Graze\DiffRenderer\Test\TestCase;
use Graze\DiffRenderer\Wrap\Wrapper;
use Mockery;
use Symfony\Component\Console\Output\ConsoleOutput;

class DiffConsoleOutputTest extends TestCase
{
    /** @var DiffConsoleOutput */
    private $console;
    /** @var mixed */
    private $output;
    /** @var Terminal */
    private $terminal;
    /** @var mixed */
    private $wrapper;
    /** @var mixed */
    private $dimensions;

    public function setUp()
    {
        $this->output = Mockery::mock(ConsoleOutput::class);
        $this->wrapper = Mockery::mock(Wrapper::class)->makePartial();
        $this->dimensions = Mockery::mock(DimensionsInterface::class);
        $this->terminal = new Terminal(null, $this->dimensions);
        $this->console = new DiffConsoleOutput($this->output, $this->terminal, $this->wrapper);
    }

    /**
     * @param int $width
     * @param int $height
     */
    private function setUpDimensions($width = 80, $height = 50)
    {
        $this->dimensions->shouldReceive('getWidth')
                         ->andReturn($width);
        $this->dimensions->shouldReceive('getHeight')
                         ->andReturn($height);
    }

    public function testTrim()
    {
        $this->assertFalse($this->console->isTrim());

        $this->console->setTrim(true);

        $this->assertTrue($this->console->isTrim());
    }

    public function testSingleWrite()
    {
        $this->setUpDimensions();
        $this->output->shouldReceive('write')
                     ->with('sample text', false, 0)
                     ->once();

        $this->console->write('sample text');

        $this->assertTrue(true);
    }

    public function testMultipleWrite()
    {
        $this->setUpDimensions();
        $this->output->shouldReceive('write')
                     ->with(['first', 'second'], true, 0)
                     ->once();

        $this->console->writeln(['first', 'second']);

        $this->assertTrue(true);
    }

    public function testUpdate()
    {
        $this->setUpDimensions();
        $this->output->shouldReceive('write')
                     ->with(['first', 'second'], false, 0)
                     ->once();
        $this->console->reWrite(['first', 'second']);

        $this->assertTrue(true);
    }

    public function testUpdateOverwrite()
    {
        $this->setUpDimensions();
        $this->output->shouldReceive('write')
                     ->with(['first', 'second'], false, 0)
                     ->once();
        $this->console->reWrite(['first', 'second']);

        $this->output->shouldReceive('write')
                     ->with("\e[1A\r\e[5C\e[K thing\n", false, 0)
                     ->once();
        $this->console->reWrite(['first thing', 'second']);

        $this->assertTrue(true);
    }

    public function testUpdateWithStyling()
    {
        $this->setUpDimensions();
        $this->output->shouldReceive('write')
                     ->with(['<info>first</info>', '<error>second</error>'], false, 0)
                     ->once();
        $this->console->reWrite(['<info>first</info>', '<error>second</error>']);

        $this->output->shouldReceive('write')
                     ->with("\e[1A\r\e[5C\e[K thing\n", false, 0)
                     ->once();
        $this->console->reWrite(['<info>first</info> thing', '<error>second</error>']);

        $this->output->shouldReceive('write')
                     ->with("\e[1A\r\e[5C\e[K<info> thing</info>\n", false, 0)
                     ->once();
        $this->console->reWrite(['<info>first thing</info>', '<error>second</error>']);

        $this->assertTrue(true);
    }

    public function testUpdateWithStyleReplacement()
    {
        $this->setUpDimensions();
        $this->output->shouldReceive('write')
                     ->with(['<info>first</info>', '<error>second</error>'], false, 0)
                     ->once();
        $this->console->reWrite(['<info>first</info>', '<error>second</error>']);

        $this->output->shouldReceive('write')
                     ->with("\e[1A\r\e[K<info>new</info> thing\n\e[K<error>fish</error>", false, 0)
                     ->once();
        $this->console->reWrite(['<info>new</info> thing', '<error>fish</error>']);

        $this->assertTrue(true);
    }

    public function testUpdateWithNewLine()
    {
        $this->setUpDimensions();
        $this->output->shouldReceive('write')
                     ->with(['first', 'second'], true, 0)
                     ->once();
        $this->console->reWrite(['first', 'second'], true);

        $this->output->shouldReceive('write')
                     ->with("\e[2A\r\e[5C\e[K thing\n", true, 0)
                     ->once();
        $this->console->reWrite(['first thing', 'second'], true);

        $this->assertTrue(true);
    }

    public function testBlankLines()
    {
        $this->setUpDimensions();
        $this->output->shouldReceive('write')
                     ->with(['first', 'second', 'third', 'fourth'], false, 0)
                     ->once();
        $this->console->reWrite(['first', 'second', 'third', 'fourth']);

        $this->output->shouldReceive('write')
                     ->with("\e[3A\r\e[Knew\n\n\n", false, 0)
                     ->once();
        $this->console->reWrite(['new', 'second', 'third', 'fourth']);

        $this->assertTrue(true);
    }

    public function testWrappedLines()
    {
        $this->setUpDimensions();
        $this->wrapper->shouldReceive('wrap')
                      ->with(['123456789012345'])
                      ->once()
                      ->andReturn(['1234567890', '12345']);

        $this->output->shouldReceive('write')
                     ->with(['1234567890', '12345'], false, 0)
                     ->once();
        $this->console->reWrite(['123456789012345']);

        $this->wrapper->shouldReceive('wrap')
                      ->with(['123cake   12345'])
                      ->once()
                      ->andReturn(['123cake   ', '12345']);

        $this->output->shouldReceive('write')
                     ->with("\e[1A\r\e[3C\e[Kcake   \n", false, 0)
                     ->once();
        $this->console->reWrite(['123cake   12345']);
    }

    public function testNewlyWrappingLines()
    {
        $this->setUpDimensions();
        $this->wrapper->shouldReceive('wrap')
                      ->with(['1234567890', '1234567890'])
                      ->once()
                      ->andReturn(['1234567890', '1234567890']);
        $this->output->shouldReceive('write')
                     ->with(['1234567890', '1234567890'], false, 0)
                     ->once();
        $this->console->reWrite(['1234567890', '1234567890']);

        $this->wrapper->shouldReceive('wrap')
                      ->with(['123456789012345', '123456789012345'])
                      ->once()
                      ->andReturn(['1234567890', '12345', '1234567890', '12345']);
        $this->output->shouldReceive('write')
                     ->with("\e[1A\r\n\e[5C\e[K\n\e[K1234567890\n\e[K12345", false, 0)
                     ->once();
        $this->console->reWrite(['123456789012345', '123456789012345']);
    }

    public function testTrimmedLines()
    {
        $this->setUpDimensions();
        $this->console->setTrim(true);

        $this->wrapper->shouldReceive('trim')
                      ->with(['123456789012345'])
                      ->once()
                      ->andReturn(['1234567890']);

        $this->output->shouldReceive('write')
                     ->with(['1234567890'], false, 0)
                     ->once();
        $this->console->reWrite(['123456789012345']);

        $this->wrapper->shouldReceive('trim')
                      ->with(['123cake   12345'])
                      ->once()
                      ->andReturn(['123cake   ']);

        $this->output->shouldReceive('write')
                     ->with("\r\e[3C\e[Kcake   ", false, 0)
                     ->once();
        $this->console->reWrite(['123cake   12345']);
    }

    public function testLineTruncationBasedOnTerminalSize()
    {
        $this->setUpDimensions(80, 5);

        $this->output->shouldReceive('write')
                     ->with(['first', 'second', 'third', 'fourth', 'fifth'], false, 0)
                     ->once();
        $this->console->reWrite(['first', 'second', 'third', 'fourth', 'fifth']);

        $this->output->shouldReceive('write')
                     ->with("\e[4A\r\e[Ksecond\n\e[Kthird\n\e[Kfourth\n\e[1C\e[Kifth\n\e[Ksixth", false, 0)
                     ->once();
        $this->console->reWrite(['first', 'second', 'third', 'fourth', 'fifth', 'sixth']);
    }

    public function testLineTruncationWithNewLine()
    {
        $this->setUpDimensions(80, 6);

        $this->output->shouldReceive('write')
                     ->with(['first', 'second', 'third', 'fourth', 'fifth'], true, 0)
                     ->once();
        $this->console->reWrite(['first', 'second', 'third', 'fourth', 'fifth'], true);

        $this->output->shouldReceive('write')
                     ->with("\e[5A\r\e[Ksecond\n\e[Kthird\n\e[Kfourth\n\e[1C\e[Kifth\n\e[Ksixth", true, 0)
                     ->once();
        $this->console->reWrite(['first', 'second', 'third', 'fourth', 'fifth', 'sixth'], true);
    }
}

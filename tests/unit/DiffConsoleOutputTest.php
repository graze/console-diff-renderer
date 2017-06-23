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
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DiffConsoleOutputTest extends TestCase
{
    const DEFAULT_OPTIONS = 34;

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
    /** @var mixed */
    private $formatter;
    /** @var string[] */
    private $replacements;

    public function setUp()
    {
        $this->formatter = Mockery::mock(OutputFormatterInterface::class);
        $auto = '';
        $this->formatter->shouldReceive('format')
                        ->with(Mockery::on(function ($string) use (&$auto) {
                            $auto = $string;
                            return true;
                        }))
                        ->andReturnUsing(function () use (&$auto) {
                            if (isset($this->replacements[$auto])) {
                                return $this->replacements[$auto];
                            }
                            return $auto;
                        });
        $this->output = Mockery::mock(OutputInterface::class);
        $this->output->shouldReceive('getFormatter')
                     ->andReturn($this->formatter);
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
        $this->wrapper->shouldReceive('getWidth')
                      ->andReturn($width);
        $this->dimensions->shouldReceive('getHeight')
                         ->andReturn($height);
    }

    public function testGetTerminal()
    {
        $this->assertSame($this->terminal, $this->console->getTerminal());
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

    public function testVerbosityIsHandledBeforeOutput()
    {
        $this->setUpDimensions();
        $this->output->shouldReceive('getVerbosity')
                     ->andReturn(OutputInterface::VERBOSITY_NORMAL);

        $this->output->shouldNotReceive('write');

        $this->console->reWrite('no write', false, OutputInterface::VERBOSITY_VERBOSE | OutputInterface::OUTPUT_NORMAL);
    }

    public function testVerbosityUsesOutputVerbosity()
    {
        $this->setUpDimensions();
        $this->output->shouldReceive('getVerbosity')
                     ->andReturn(OutputInterface::VERBOSITY_VERBOSE);

        $this->output->shouldReceive('write')
                     ->with(['test'], false, OutputInterface::VERBOSITY_VERBOSE | OutputInterface::OUTPUT_RAW)
                     ->once();

        $this->console->reWrite('test');
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

    public function testReWrite()
    {
        $this->setUpDimensions();
        $this->output->shouldReceive('getVerbosity')
                     ->andReturn(OutputInterface::VERBOSITY_NORMAL);

        $this->output->shouldReceive('write')
                     ->with(['first', 'second'], false, static::DEFAULT_OPTIONS)
                     ->once();
        $this->console->reWrite(['first', 'second']);

        $this->assertTrue(true);
    }

    public function testUpdateOverwrite()
    {
        $this->setUpDimensions();
        $this->output->shouldReceive('getVerbosity')
                     ->andReturn(OutputInterface::VERBOSITY_NORMAL);

        $this->output->shouldReceive('write')
                     ->with(['first', 'second'], false, static::DEFAULT_OPTIONS)
                     ->once();
        $this->console->reWrite(['first', 'second']);

        $this->output->shouldReceive('write')
                     ->with("\e[1A\r\e[5C\e[K thing\n\r", false, static::DEFAULT_OPTIONS)
                     ->once();
        $this->console->reWrite(['first thing', 'second']);

        $this->assertTrue(true);
    }

    public function testUpdateWithFormatting()
    {
        $this->setUpDimensions();
        $this->output->shouldReceive('getVerbosity')
                     ->andReturn(OutputInterface::VERBOSITY_NORMAL);

        $this->replacements['<info>first</info>'] = 'INFO[first]';
        $this->replacements['<error>second</error>'] = 'ERROR[second]';

        $this->output->shouldReceive('write')
                     ->with(["INFO[first]", "ERROR[second]"], false, static::DEFAULT_OPTIONS)
                     ->once();
        $this->console->reWrite(['<info>first</info>', '<error>second</error>']);

        $this->replacements['<info>first</info> thing'] = 'INFO[first] thing';

        $this->output->shouldReceive('write')
                     ->with("\e[1A\r\e[11C\e[K thing\n\r", false, static::DEFAULT_OPTIONS)
                     ->once();
        $this->console->reWrite(['<info>first</info> thing', '<error>second</error>']);

        $this->replacements['<info>first thing</info>'] = 'INFO[first thing]';

        $this->output->shouldReceive('write')
                     ->with("\e[1A\r\e[10C\e[K thing]\n\r", false, static::DEFAULT_OPTIONS)
                     ->once();
        $this->console->reWrite(['<info>first thing</info>', '<error>second</error>']);

        $this->assertTrue(true);
    }

    public function testUpdateWithNewLine()
    {
        $this->setUpDimensions();
        $this->output->shouldReceive('getVerbosity')
                     ->andReturn(OutputInterface::VERBOSITY_NORMAL);

        $this->output->shouldReceive('write')
                     ->with(['first', 'second'], true, static::DEFAULT_OPTIONS)
                     ->once();
        $this->console->reWrite(['first', 'second'], true);

        $this->output->shouldReceive('write')
                     ->with("\e[2A\r\e[5C\e[K thing\n\r", true, static::DEFAULT_OPTIONS)
                     ->once();
        $this->console->reWrite(['first thing', 'second'], true);

        $this->assertTrue(true);
    }

    public function testBlankLines()
    {
        $this->setUpDimensions();
        $this->output->shouldReceive('getVerbosity')
                     ->andReturn(OutputInterface::VERBOSITY_NORMAL);

        $this->output->shouldReceive('write')
                     ->with(['first', 'second', 'third', 'fourth'], false, static::DEFAULT_OPTIONS)
                     ->once();
        $this->console->reWrite(['first', 'second', 'third', 'fourth']);

        $this->output->shouldReceive('write')
                     ->with("\e[3A\r\e[Knew\n\r\n\r\n\r", false, static::DEFAULT_OPTIONS)
                     ->once();
        $this->console->reWrite(['new', 'second', 'third', 'fourth']);

        $this->assertTrue(true);
    }

    public function testWrappedLines()
    {
        $this->setUpDimensions();
        $this->output->shouldReceive('getVerbosity')
                     ->andReturn(OutputInterface::VERBOSITY_NORMAL);

        $this->wrapper->shouldReceive('wrap')
                      ->with(['123456789012345'])
                      ->once()
                      ->andReturn(['1234567890', '12345']);

        $this->output->shouldReceive('write')
                     ->with(['1234567890', '12345'], false, static::DEFAULT_OPTIONS)
                     ->once();
        $this->console->reWrite(['123456789012345']);

        $this->wrapper->shouldReceive('wrap')
                      ->with(['123cake   12345'])
                      ->once()
                      ->andReturn(['123cake   ', '12345']);

        $this->output->shouldReceive('write')
                     ->with("\e[1A\r\e[3C\e[Kcake   \n\r", false, static::DEFAULT_OPTIONS)
                     ->once();
        $this->console->reWrite(['123cake   12345']);
    }

    public function testNewlyWrappingLines()
    {
        $this->setUpDimensions();
        $this->output->shouldReceive('getVerbosity')
                     ->andReturn(OutputInterface::VERBOSITY_NORMAL);

        $this->wrapper->shouldReceive('wrap')
                      ->with(['1234567890', '1234567890'])
                      ->once()
                      ->andReturn(['1234567890', '1234567890']);
        $this->output->shouldReceive('write')
                     ->with(['1234567890', '1234567890'], false, static::DEFAULT_OPTIONS)
                     ->once();
        $this->console->reWrite(['1234567890', '1234567890']);

        $this->wrapper->shouldReceive('wrap')
                      ->with(['123456789012345', '123456789012345'])
                      ->once()
                      ->andReturn(['1234567890', '12345', '1234567890', '12345']);
        $this->output->shouldReceive('write')
                     ->with("\e[1A\r\n\r\e[5C\e[K\n\r\e[K1234567890\n\r\e[K12345", false, static::DEFAULT_OPTIONS)
                     ->once();
        $this->console->reWrite(['123456789012345', '123456789012345']);
    }

    public function testTrimmedLines()
    {
        $this->setUpDimensions();
        $this->output->shouldReceive('getVerbosity')
                     ->andReturn(OutputInterface::VERBOSITY_NORMAL);

        $this->console->setTrim(true);

        $this->wrapper->shouldReceive('trim')
                      ->with(['123456789012345'])
                      ->once()
                      ->andReturn(['1234567890']);

        $this->output->shouldReceive('write')
                     ->with(['1234567890'], false, static::DEFAULT_OPTIONS)
                     ->once();
        $this->console->reWrite(['123456789012345']);

        $this->wrapper->shouldReceive('trim')
                      ->with(['123cake   12345'])
                      ->once()
                      ->andReturn(['123cake   ']);

        $this->output->shouldReceive('write')
                     ->with("\r\e[3C\e[Kcake   ", false, static::DEFAULT_OPTIONS)
                     ->once();
        $this->console->reWrite(['123cake   12345']);
    }

    public function testLineTruncationBasedOnTerminalSize()
    {
        $this->setUpDimensions(80, 5);
        $this->output->shouldReceive('getVerbosity')
                     ->andReturn(OutputInterface::VERBOSITY_NORMAL);

        $this->output->shouldReceive('write')
                     ->with(['first', 'second', 'third', 'fourth', 'fifth'], false, static::DEFAULT_OPTIONS)
                     ->once();
        $this->console->reWrite(['first', 'second', 'third', 'fourth', 'fifth']);

        $this->output->shouldReceive('write')
                     ->with(
                         "\e[4A\r\e[Ksecond\n\r\e[Kthird\n\r\e[Kfourth\n\r\e[1C\e[Kifth\n\r\e[Ksixth",
                         false,
                         static::DEFAULT_OPTIONS
                     )
                     ->once();
        $this->console->reWrite(['first', 'second', 'third', 'fourth', 'fifth', 'sixth']);
    }

    public function testLineTruncationWithNewLine()
    {
        $this->setUpDimensions(80, 6);
        $this->output->shouldReceive('getVerbosity')
                     ->andReturn(OutputInterface::VERBOSITY_NORMAL);

        $this->output->shouldReceive('write')
                     ->with(['first', 'second', 'third', 'fourth', 'fifth'], true, static::DEFAULT_OPTIONS)
                     ->once();
        $this->console->reWrite(['first', 'second', 'third', 'fourth', 'fifth'], true);

        $this->output->shouldReceive('write')
                     ->with(
                         "\e[5A\r\e[Ksecond\n\r\e[Kthird\n\r\e[Kfourth\n\r\e[1C\e[Kifth\n\r\e[Ksixth",
                         true,
                         static::DEFAULT_OPTIONS
                     )
                     ->once();
        $this->console->reWrite(['first', 'second', 'third', 'fourth', 'fifth', 'sixth'], true);
    }

    public function testSplitNewLines()
    {
        $this->setUpDimensions();
        $this->output->shouldReceive('getVerbosity')
                     ->andReturn(OutputInterface::VERBOSITY_NORMAL);

        $this->output->shouldReceive('write')
                     ->with(['first', 'second'], false, static::DEFAULT_OPTIONS)
                     ->once();
        $this->console->reWrite("first\nsecond");
    }
}

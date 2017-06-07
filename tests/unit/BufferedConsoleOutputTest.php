<?php

namespace Graze\BufferedConsole\Test\Unit;

use Graze\BufferedConsole\BufferedConsoleOutput;
use Graze\BufferedConsole\Test\TestCase;
use Mockery;
use Symfony\Component\Console\Output\ConsoleOutput;

class BufferedConsoleOutputTest extends TestCase
{
    /** @var BufferedConsoleOutput */
    private $console;
    private $output;

    public function setUp()
    {
        $this->output = Mockery::mock(ConsoleOutput::class);
        $this->console = new BufferedConsoleOutput($this->output);
    }

    public function testSingleWrite()
    {
        $this->output->shouldReceive('write')
                     ->with('sample text', false, 0)
                     ->once();

        $this->console->write('sample text');

        $this->assertTrue(true);
    }

    public function testMultipleWrite()
    {
        $this->output->shouldReceive('write')
                     ->with(['first', 'second'], true, 0)
                     ->once();

        $this->console->writeln(['first', 'second']);

        $this->assertTrue(true);
    }

    public function testUpdate()
    {
        $this->output->shouldReceive('write')
                     ->with(['first', 'second'], false, 0)
                     ->once();
        $this->console->reWrite(['first', 'second']);

        $this->assertTrue(true);
    }

    public function testUpdateOverwrite()
    {
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
        $this->output->shouldReceive('write')
                     ->with(['<info>first</info>', '<error>second</error>'], false, 0)
                     ->once();
        $this->console->reWrite(['<info>first</info>', '<error>second</error>']);

        $this->output->shouldReceive('write')
                     ->with("\e[1A\r\e[5C\e[K thing\n", false, 0)
                     ->once();
        $this->console->reWrite(['<info>first</info> thing', '<error>second</error>']);

        $this->output->shouldReceive('write')
                     ->with("\e[1A\r\e[5C\e[K thing</info>\n", false, 0)
                     ->once();
        $this->console->reWrite(['<info>first thing</info>', '<error>second</error>']);

        $this->assertTrue(true);
    }

    public function testUpdateWithNewLine()
    {
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
}

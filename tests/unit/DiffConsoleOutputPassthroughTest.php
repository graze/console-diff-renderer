<?php

namespace Graze\DiffRenderer\Test\Unit;

use Graze\DiffRenderer\DiffConsoleOutput;
use Graze\DiffRenderer\Terminal\TerminalInterface;
use Graze\DiffRenderer\Test\TestCase;
use Mockery;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DiffConsoleOutputPassThroughTest extends TestCase
{
    /** @var DiffConsoleOutput */
    private $diffOutput;
    /** @var mixed */
    private $output;
    /** @var mixed */
    private $terminal;

    public function setUp()
    {
        parent::setUp();

        $this->output = Mockery::mock(OutputInterface::class);
        $this->diffOutput = new DiffConsoleOutput($this->output, $this->terminal);
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(OutputInterface::class, $this->diffOutput);
    }

    public function testGetVerbosity()
    {
        $this->output->shouldReceive('getVerbosity')
                     ->with()
                     ->andReturn(OutputInterface::VERBOSITY_VERY_VERBOSE);

        $this->assertEquals(OutputInterface::VERBOSITY_VERY_VERBOSE, $this->diffOutput->getVerbosity());
    }

    public function testGetFormatter()
    {
        $formatter = Mockery::mock(OutputFormatterInterface::class);
        $this->output->shouldReceive('getFormatter')
                     ->with()
                     ->andReturn($formatter);

        $this->assertEquals($formatter, $this->diffOutput->getFormatter());
    }

    public function testIsDebug()
    {
        $this->output->shouldReceive('isDebug')
                     ->with()
                     ->andReturn(false);

        $this->assertEquals(false, $this->diffOutput->isDebug());
    }

    public function testIsDecorated()
    {
        $this->output->shouldReceive('isDecorated')
                     ->with()
                     ->andReturn(true);

        $this->assertEquals(true, $this->diffOutput->isDecorated());
    }

    public function testIsQuiet()
    {
        $this->output->shouldReceive('isQuiet')
                     ->with()
                     ->andReturn(false);

        $this->assertEquals(false, $this->diffOutput->isQuiet());
    }

    public function testIsVerbose()
    {
        $this->output->shouldReceive('isVerbose')
                     ->with()
                     ->andReturn(true);

        $this->assertEquals(true, $this->diffOutput->isVerbose());
    }

    public function testIsVeryVerbose()
    {
        $this->output->shouldReceive('isVeryVerbose')
                     ->with()
                     ->andReturn(false);

        $this->assertEquals(false, $this->diffOutput->isVeryVerbose());
    }

    public function testSetVerbosity()
    {
        $this->output->shouldReceive('setVerbosity')
                     ->with(OutputInterface::VERBOSITY_NORMAL)
                     ->once();

        $this->diffOutput->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
    }

    public function testSetDecorated()
    {
        $this->output->shouldReceive('setDecorated')
                     ->with(true)
                     ->once();

        $this->diffOutput->setDecorated(true);
    }

    public function testSetFormatter()
    {
        $formatter = Mockery::mock(OutputFormatterInterface::class);
        $this->output->shouldReceive('setFormatter')
                     ->with($formatter)
                     ->once();

        $this->diffOutput->setFormatter($formatter);
    }
}

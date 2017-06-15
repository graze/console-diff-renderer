<?php

namespace Graze\DiffRenderer\Tests\Unit;

use Graze\DiffRenderer\Terminal\DimensionsInterface;
use Graze\DiffRenderer\Terminal\TerminalDimensions;
use Graze\DiffRenderer\Test\TestCase;

class TerminalDimensionsTest extends TestCase
{
    public function testEnvironmentVariables()
    {
        putenv('COLUMNS=70');
        putenv('LINES=30');

        $dimensions = new TerminalDimensions();
        $this->assertEquals(70, $dimensions->getWidth());
        $this->assertEquals(30, $dimensions->getHeight());
    }
}

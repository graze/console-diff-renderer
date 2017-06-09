<?php

namespace Graze\BufferedConsole\Terminal;

interface DimensionsInterface
{
    const DEFAULT_WIDTH  = 80;
    const DEFAULT_HEIGHT = 50;

    /**
     * @return int
     */
    public function getWidth();

    /**
     * @return int
     */
    public function getHeight();
}

<?php

namespace Graze\BufferedConsole\Terminal;

class ANSI implements CursorInterface
{
    const ESCAPE = "\e";

    const CODE_MOVE_POSITION   = '[%d;%dH'; // line, column
    const CODE_MOVE_UP_LINES   = '[%dA'; // lines
    const CODE_MOVE_DOWN_LINES = '[%dB'; // lines
    const CODE_MOVE_FORWARD    = '[%dC'; // columns
    const CODE_MOVE_BACKWARDS  = '[%dD'; // columns

    const CODE_ERASE_TO_END_OF_LINE   = '[K';
    const CODE_ERASE_TO_START_OF_LINE = '[1K';
    const CODE_ERASE_LINE             = '[2K';
    const CODE_ERASE_DOWN             = '[J';
    const CODE_ERASE_UP               = '[1J';
    const CODE_ERASE_SCREEN           = '[2J';

    /**
     * @param int $line
     * @param int $column
     *
     * @return string
     */
    public function move($line, $column)
    {
        return static::ESCAPE . sprintf(static::CODE_MOVE_POSITION, $line, $column);
    }

    /**
     * @param int $lines
     *
     * @return string
     */
    public function moveUp($lines)
    {
        return static::ESCAPE . sprintf(static::CODE_MOVE_UP_LINES, $lines);
    }

    /**
     * @param int $lines
     *
     * @return string
     */
    public function moveDown($lines)
    {
        return static::ESCAPE . sprintf(static::CODE_MOVE_DOWN_LINES, $lines);
    }

    /**
     * @param int $columns
     *
     * @return string
     */
    public function moveLeft($columns)
    {
        return static::ESCAPE . sprintf(static::CODE_MOVE_BACKWARDS, $columns);
    }

    /**
     * @param int $columns
     *
     * @return string
     */
    public function moveRight($columns)
    {
        return static::ESCAPE . sprintf(static::CODE_MOVE_FORWARD, $columns);
    }

    /**
     * @return string
     */
    public function eraseToEnd()
    {
        return static::ESCAPE . static::CODE_ERASE_TO_END_OF_LINE;
    }

    /**
     * @return string
     */
    public function eraseToStart()
    {
        return static::ESCAPE . static::CODE_ERASE_TO_START_OF_LINE;
    }

    /**
     * @return string
     */
    public function eraseDown()
    {
        return static::ESCAPE . static::CODE_ERASE_DOWN;
    }

    /**
     * @return string
     */
    public function eraseUp()
    {
        return static::ESCAPE . static::CODE_ERASE_UP;
    }

    /**
     * @return string
     */
    public function eraseScreen()
    {
        return static::ESCAPE . static::CODE_ERASE_SCREEN;
    }
}

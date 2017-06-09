<?php

namespace Graze\BufferedConsole\Terminal;

use Symfony\Component\Console\Terminal as SymfonyTerminal;

class Terminal implements TerminalInterface
{
    /** @var CursorInterface */
    protected $cursor;
    /** @var int */
    private $width = self::DEFAULT_WIDTH;
    /** @var int */
    private $height = self::DEFAULT_HEIGHT;

    /**
     * Terminal will provide cursor and dimension information to the outputter
     *
     * @param CursorInterface|null $cursor
     * @param SymfonyTerminal      $terminal
     */
    public function __construct(CursorInterface $cursor = null, SymfonyTerminal $terminal = null)
    {
        $this->cursor = $cursor ?: new ANSI();
        $terminal = $terminal ?: new SymfonyTerminal();
        $this->width = $terminal->getWidth();
        $this->height = $terminal->getHeight();
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Move the cursor to y,x
     *
     * @param int $line
     * @param int $column
     *
     * @return string
     */
    public function move($line, $column)
    {
        return $this->cursor->move($line, $column);
    }

    /**
     * Move up n Lines
     *
     * @param int $lines
     *
     * @return string
     */
    public function moveUp($lines)
    {
        return $this->cursor->moveUp($lines);
    }

    /**
     * Move down n Lines
     *
     * @param int $lines
     *
     * @return string
     */
    public function moveDown($lines)
    {
        return $this->cursor->moveDown($lines);
    }

    /**
     * Move left n Columns
     *
     * @param int $columns
     *
     * @return string
     */
    public function moveLeft($columns)
    {
        return $this->cursor->moveLeft($columns);
    }

    /**
     * Move right n Columns
     *
     * @param int $columns
     *
     * @return string
     */
    public function moveRight($columns)
    {
        return $this->cursor->moveRight($columns);
    }

    /**
     * Erase to the end of the line
     *
     * @return string
     */
    public function eraseToEnd()
    {
        return $this->cursor->eraseToEnd();
    }

    /**
     * Erase to the start of the line
     *
     * @return string
     */
    public function eraseToStart()
    {
        return $this->cursor->eraseToStart();
    }

    /**
     * Erase Down
     *
     * @return string
     */
    public function eraseDown()
    {
        return $this->cursor->eraseDown();
    }

    /**
     * Erase Up
     *
     * @return string
     */
    public function eraseUp()
    {
        return $this->cursor->eraseUp();
    }

    /**
     * Erase entire screen
     *
     * @return string
     */
    public function eraseScreen()
    {
        return $this->cursor->eraseScreen();
    }
}

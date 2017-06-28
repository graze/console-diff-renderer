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

namespace Graze\DiffRenderer\Terminal;

class Terminal implements TerminalInterface
{
    /** @var CursorInterface */
    protected $cursor;
    /** @var DimensionsInterface */
    private $dimensions;

    /**
     * Terminal will provide cursor and dimension information to the outputter
     *
     * @param CursorInterface|null $cursor
     * @param DimensionsInterface  $dimensions
     */
    public function __construct(CursorInterface $cursor = null, DimensionsInterface $dimensions = null)
    {
        $this->cursor = $cursor ?: new ANSI();
        $this->dimensions = $dimensions ?: new TerminalDimensions();
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->dimensions->getWidth();
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->dimensions->getHeight();
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

    /**
     * @return string
     */
    public function hideCursor()
    {
        return $this->cursor->hideCursor();
    }

    /**
     * @return string
     */
    public function showCursor()
    {
        return $this->cursor->showCursor();
    }

    /**
     * Filter takes a string with Cursor movements and filters them out
     *
     * @param string $string
     * @param string $replacement Optional replacement for each item
     *
     * @return string
     */
    public function filter($string, $replacement = '')
    {
        return $this->cursor->filter($string, $replacement);
    }

    /**
     * Get the current formatting for this string
     *
     * @param string $string
     *
     * @return string
     */
    public function getCurrentFormatting($string)
    {
        return $this->cursor->getCurrentFormatting($string);
    }
}

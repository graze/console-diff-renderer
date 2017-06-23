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

namespace Graze\DiffRenderer\Wrap;

use Graze\DiffRenderer\Terminal\TerminalInterface;

class Wrapper
{
    const REPLACEMENT_CHAR = "\6";

    /** @var TerminalInterface */
    private $terminal;

    /**
     * Wrapper constructor.
     *
     * @param TerminalInterface $terminal
     */
    public function __construct(TerminalInterface $terminal)
    {
        $this->terminal = $terminal;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->terminal->getWidth();
    }

    /**
     * @param string[] $input
     *
     * @return string[]
     */
    public function wrap(array $input)
    {
        $out = [];
        foreach ($input as $line) {
            foreach ($this->chunk($line) as $new) {
                $out[] = $new;
            }
        }
        return $out;
    }

    /**
     * @param string[] $input
     *
     * @return string[]
     */
    public function trim(array $input)
    {
        $out = [];
        foreach ($input as $line) {
            $chunk = $this->chunk($line);
            $out[] = reset($chunk);
        }
        return $out;
    }

    /**
     * @param string $line
     *
     * @return string[]
     */
    private function chunk($line)
    {
        $width = $this->getWidth();
        if (mb_strlen($line) <= $width) {
            return [$line];
        }
        $stripped = $this->terminal->filter($line, static::REPLACEMENT_CHAR);
        $offset = 0;
        $out = [];

        for ($i = 0, $j = 0; $i <= mb_strlen($stripped); $i++) {
            if (mb_substr($stripped, $i, 1) != static::REPLACEMENT_CHAR) {
                if ($j > 0 && $j % $width == 0) {
                    $out[] = mb_substr($line, $offset, $i - $offset);
                    $offset = $i;
                }
                $j++;
            }
        }

        // return any remaining entries
        if ($offset != $i - 1) {
            $out[] = mb_substr($line, $offset);
        }
        return $out;
    }
}

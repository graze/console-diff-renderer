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

use Graze\DiffRenderer\Terminal\DimensionsInterface;

class Wrapper
{
    /** @var int */
    private $width = DimensionsInterface::DEFAULT_WIDTH;

    /**
     * Wrapper constructor.
     *
     * @param int $width
     */
    public function __construct($width)
    {
        $this->width = $width;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     *
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
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
        if (mb_strlen($line) <= $this->width) {
            return [$line];
        }
        $stripped = strip_tags($line);
        $offset = 0;
        $out = [];

        // create a stripped tags version of the string
        // loop through both and only move the counter if both characters are equal
        // yield when we get to <width> for the stripped tags version
        for ($i = 0, $j = 0; $i <= mb_strlen($stripped); $j++) {
            if (mb_substr($stripped, $i, 1) === mb_substr($line, $j, 1)) {
                if ($i >= $this->width && $i % $this->width == 0) {
                    $out[] = mb_substr($line, $offset, $j - $offset);
                    $offset = $j;
                }
                $i++;
            }
        }

        // return any remaining entries
        if ($offset != $j - 1) {
            $out[] = mb_substr($line, $offset);
        }
        return $out;
    }
}

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

namespace Graze\DiffRenderer\Diff;

use Graze\DiffRenderer\Terminal\ANSI;
use Graze\DiffRenderer\Terminal\CursorInterface;

class ConsoleDiff extends FirstDiff
{
    const REPLACEMENT_CHAR = "\6";

    /** @var CursorInterface */
    private $cursor;

    /**
     * ConsoleDiff constructor.
     *
     * @param CursorInterface|null $cursor The cursor used to move around the screen
     */
    public function __construct(CursorInterface $cursor = null)
    {
        $this->cursor = $cursor ?: new ANSI();
    }

    /**
     * Loop through each line, and produce a difference report of what to write out to the terminal
     *
     * Assumes the beginning of old and new are the same line
     *
     * @param string[] $old
     * @param string[] $new
     *
     * @return array [[:col, :str]]
     */
    public function lines(array $old, array $new)
    {
        $diff = parent::lines($old, $new);

        $len = count($new);
        for ($i = 0; $i < $len; $i++) {
            if (isset($diff[$i]) && !is_null($new[$i]) && $diff[$i]['col'] > 0) {
                $styling = $this->cursor->getCurrentFormatting(mb_substr($new[$i], 0, $diff[$i]['col']));
                $diff[$i]['col'] = mb_strlen($this->cursor->filter(mb_substr($new[$i], 0, $diff[$i]['col'])));
                if (mb_strlen($styling) > 0) {
                    $diff[$i]['str'] = $styling . $diff[$i]['str'];
                }
            }
        }

        return $diff;
    }

    /**
     * Looks through a line to find the character to of the first difference between the 2 strings.
     *
     * If no difference is found, returns -1
     *
     * @param string $old
     * @param string $new
     *
     * @return int
     */
    public function firstDifference($old, $new)
    {
        // loop through old and new character by character and compare
        $oldLen = mb_strlen($old);
        $newLen = mb_strlen($new);

        if ($oldLen === 0) {
            return 0;
        }

        $oldStripped = $this->cursor->filter($old, static::REPLACEMENT_CHAR);
        $newStripped = $this->cursor->filter($new, static::REPLACEMENT_CHAR);
        $lastReal = 0;

        for ($i = 0; $i < $oldLen && $i < $newLen; $i++) {
            if (mb_substr($old, $i, 1) !== mb_substr($new, $i, 1)) {
                if (($i > 0)
                    && ((mb_substr($oldStripped, $i - 1, 1) === static::REPLACEMENT_CHAR)
                        || (mb_substr($newStripped, $i - 1, 1) === static::REPLACEMENT_CHAR)
                    )
                ) {
                    return $lastReal > 0 ? $lastReal + 1 : 0;
                }
                return $i;
            } elseif (mb_substr($oldStripped, $i, 1) !== static::REPLACEMENT_CHAR) {
                $lastReal = $i;
            }
        }
        if ($i < $oldLen || $i < $newLen) {
            return $i;
        }
        return -1;
    }
}

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

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

class ANSI implements CursorInterface
{
    const ESCAPE = "\e";

    const CODE_MOVE_POSITION       = '[%d;%dH'; // line, column
    const CODE_MOVE_POSITION_FORCE = '[%d;%df'; // line, column
    const CODE_MOVE_UP_LINES       = '[%dA'; // lines
    const CODE_MOVE_DOWN_LINES     = '[%dB'; // lines
    const CODE_MOVE_FORWARD        = '[%dC'; // columns
    const CODE_MOVE_BACKWARDS      = '[%dD'; // columns

    const CODE_ERASE_TO_END_OF_LINE   = '[K';
    const CODE_ERASE_TO_START_OF_LINE = '[1K';
    const CODE_ERASE_LINE             = '[2K';
    const CODE_ERASE_DOWN             = '[J';
    const CODE_ERASE_UP               = '[1J';
    const CODE_ERASE_SCREEN           = '[2J';

    const CODE_HIDE_CURSOR = '[?25l';
    const CODE_SHOW_CURSOR = '[?25h';

    const REGEX_ANSI       = "/(?:\r|\e(?:\\[[0-9;]*[HfABCDKJcnRsurgim]|\\[(?:=|\?)\\[[0-9]{1,2}[hl]|[c\\(\\)78DMH]))/";
    const REGEX_FORMAT     = "/\e\\[((?:[0-9][0-9;]*|))m/";
    const REGEX_STYLE_ITEM = '/\b(?:(?:([34]8);(?:2;\d{1,3};\d{1,3};\d{1,3}|5;\d{1,3}))|(\d+)(?<!38|48))\b/';
    const REGEX_FIRST_KEY  = '/^(\d+)/';

    const STYLE_RESET = '0';

    /** @var array */
    protected $filter = [];
    /** @var array */
    private $formats;

    public function __construct()
    {
        $this->formats = [
            '1'   => ['21', '22', '39', '49'],
            '2'   => ['22', '39', '49'],
            '3'   => ['23'],
            '4'   => ['24'],
            '5'   => ['25'],
            '6'   => ['25'],
            '7'   => ['27'],
            '8'   => ['28'],
            '9'   => ['29'],
            '11'  => ['10'],
            '12'  => ['10'],
            '13'  => ['10'],
            '14'  => ['10'],
            '15'  => ['10'],
            '16'  => ['10'],
            '17'  => ['10'],
            '18'  => ['10'],
            '19'  => ['10'],
            '20'  => ['23'],
            '30'  => ['39'],
            '31'  => ['39'],
            '32'  => ['39'],
            '33'  => ['39'],
            '34'  => ['39'],
            '35'  => ['39'],
            '36'  => ['39'],
            '37'  => ['39'],
            '38'  => ['39'],
            '40'  => ['49'],
            '41'  => ['49'],
            '42'  => ['49'],
            '43'  => ['49'],
            '44'  => ['49'],
            '45'  => ['49'],
            '46'  => ['49'],
            '47'  => ['49'],
            '48'  => ['49'],
            '51'  => ['54'],
            '52'  => ['54'],
            '53'  => ['55'],
            '60'  => ['65'],
            '61'  => ['65'],
            '62'  => ['65'],
            '63'  => ['65'],
            '64'  => ['65'],
            '90'  => ['39'],
            '91'  => ['39'],
            '92'  => ['39'],
            '93'  => ['39'],
            '94'  => ['39'],
            '95'  => ['39'],
            '96'  => ['39'],
            '97'  => ['39'],
            '100' => ['49'],
            '101' => ['49'],
            '102' => ['49'],
            '103' => ['49'],
            '104' => ['49'],
            '105' => ['49'],
            '106' => ['49'],
            '107' => ['49'],
        ];
    }

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

    /**
     * @return string
     */
    public function hideCursor()
    {
        return static::ESCAPE . static::CODE_HIDE_CURSOR;
    }

    /**
     * @return string
     */
    public function showCursor()
    {
        return static::ESCAPE . static::CODE_SHOW_CURSOR;
    }

    /**
     * Filter takes a string with Cursor movements and filters them out
     *
     * @param string $string
     * @param string $replacement Optional character or string to replace specific codes with
     *
     * @return string
     */
    public function filter($string, $replacement = '')
    {
        if ($replacement !== '') {
            return preg_replace_callback(static::REGEX_ANSI, function ($matches) use ($replacement) {
                return str_repeat($replacement, mb_strlen($matches[0]));
            }, $string);
        }
        return preg_replace(static::REGEX_ANSI, $replacement, $string);
    }

    /**
     * Gets the styling that would be active at the end of this string
     *
     * @param string $string
     *
     * @return string
     */
    public function getCurrentFormatting($string)
    {
        $stack = [];
        foreach ($this->getStyleStack($string) as $style) {
            if (preg_match(static::REGEX_FIRST_KEY, $style, $matches)) {
                $key = $matches[0];

                // if this is a valid setting style, add it to the stack
                if (array_key_exists($key, $this->formats)) {
                    $stack[] = ['key' => $key, 'style' => $style];
                } else {
                    // otherwise remove all elements that this turns off from the current stack
                    if ($key === static::STYLE_RESET) {
                        $stack = [];
                    } else {
                        $stack = array_filter($stack, function ($item) use ($key) {
                            return !in_array($key, $this->formats[$item['key']]);
                        });
                    }
                }
            }
        }

        if (count($stack) === 0) {
            return '';
        }

        $items = array_map(function ($item) {
            return $item['style'];
        }, $stack);
        return sprintf(static::ESCAPE . '[%sm', implode(';', $items));
    }

    /**
     * Get all the styles in order that should be applied at the end
     *
     * @param string $string
     *
     * @return \Generator|void Iterator of numbers representing styles
     */
    private function getStyleStack($string)
    {
        if (preg_match_all(static::REGEX_FORMAT, $string, $matches)) {
            foreach ($matches[1] as $grouping) {
                if (preg_match_all(static::REGEX_STYLE_ITEM, $grouping, $styles)) {
                    foreach ($styles[0] as $style) {
                        yield $style;
                    }
                } else {
                    yield static::STYLE_RESET;
                }
            }
        }
        return;
    }
}

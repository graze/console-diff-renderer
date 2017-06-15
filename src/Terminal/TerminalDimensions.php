<?php

namespace Graze\DiffRenderer\Terminal;

/**
 * Class TerminalDimensions
 *
 * Copied from symfony/console/Terminal
 */
class TerminalDimensions implements DimensionsInterface
{
    /** @var int|null */
    private $width = null;
    /** @var int|null */
    private $height = null;
    /** @var bool */
    private $initialised = false;

    /**
     * Gets the terminal width.
     *
     * @return int
     */
    public function getWidth()
    {
        if (null === $this->width && !$this->initialised) {
            $this->refreshDimensions();
        }

        if (null === $this->width) {
            $width = getenv('COLUMNS');
            if (false !== $width) {
                $this->width = (int) trim($width);
            }
        }

        return $this->width ?: static::DEFAULT_WIDTH;
    }

    /**
     * Gets the terminal height.
     *
     * @return int
     */
    public function getHeight()
    {
        if (null === $this->height && !$this->initialised) {
            $this->refreshDimensions();
        }

        if (null === $this->height) {
            $height = getenv('LINES');
            if (false !== $height) {
                $this->height = (int) trim($height);
            }
        }

        return $this->height ?: static::DEFAULT_HEIGHT;
    }

    /**
     * Refresh the current dimensions from the terminal
     */
    public function refreshDimensions()
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            if (preg_match('/^(\d+)x(\d+)(?: \((\d+)x(\d+)\))?$/', trim(getenv('ANSICON')), $matches)) {
                // extract [w, H] from "wxh (WxH)"
                // or [w, h] from "wxh"
                $this->width = (int) $matches[1];
                $this->height = isset($matches[4]) ? (int) $matches[4] : (int) $matches[2];
            } elseif (null !== $dimensions = $this->getConsoleMode()) {
                // extract [w, h] from "wxh"
                $this->width = (int) $dimensions[0];
                $this->height = (int) $dimensions[1];
            }
        } elseif ($sttyString = $this->getSttyColumns()) {
            if (preg_match('/rows.(\d+);.columns.(\d+);/i', $sttyString, $matches)) {
                // extract [w, h] from "rows h; columns w;"
                $this->width = (int) $matches[2];
                $this->height = (int) $matches[1];
            } elseif (preg_match('/;.(\d+).rows;.(\d+).columns/i', $sttyString, $matches)) {
                // extract [w, h] from "; h rows; w columns"
                $this->width = (int) $matches[2];
                $this->height = (int) $matches[1];
            }
        }
        $this->initialised = true;
    }

    /**
     * Runs and parses mode CON if it's available, suppressing any error output.
     *
     * @return int[]|null An array composed of the width and the height or null if it could not be parsed
     */
    private function getConsoleMode()
    {
        if (!function_exists('proc_open')) {
            return null;
        }

        $spec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = proc_open('mode CON', $spec, $pipes, null, null, ['suppress_errors' => true]);
        if (is_resource($process)) {
            $info = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);

            if (preg_match('/--------+\r?\n.+?(\d+)\r?\n.+?(\d+)\r?\n/', $info, $matches)) {
                return [(int) $matches[2], (int) $matches[1]];
            }
        }
        return null;
    }

    /**
     * Runs and parses stty -a if it's available, suppressing any error output.
     *
     * @return string|null
     */
    private function getSttyColumns()
    {
        if (!function_exists('proc_open')) {
            return null;
        }

        $spec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open('stty -a | grep columns', $spec, $pipes, null, null, ['suppress_errors' => true]);
        if (is_resource($process)) {
            $info = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);

            return $info;
        }

        return null;
    }
}

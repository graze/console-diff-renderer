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

namespace Graze\DiffRenderer;

use Graze\DiffRenderer\Diff\ConsoleDiff;
use Graze\DiffRenderer\Terminal\Terminal;
use Graze\DiffRenderer\Terminal\TerminalInterface;
use Graze\DiffRenderer\Wrap\Wrapper;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This takes an array of lines to write to the console, does a different and only over-writes what has changed to the
 * console
 */
class DiffConsoleOutput implements OutputInterface
{
    /** @var string[] */
    private $buffer = [];
    /** @var ConsoleDiff */
    private $diff;
    /** @var TerminalInterface */
    private $terminal;
    /** @var OutputInterface */
    private $output;
    /** @var Wrapper */
    private $wrapper;
    /** @var bool */
    private $trim = false;

    /**
     * Constructor.
     *
     * @param OutputInterface   $output
     * @param TerminalInterface $terminal
     * @param Wrapper           $wrapper
     */
    public function __construct(
        OutputInterface $output,
        TerminalInterface $terminal = null,
        Wrapper $wrapper = null
    ) {
        $this->output = $output;
        $this->terminal = $terminal ?: new Terminal();
        $this->diff = new ConsoleDiff($terminal);
        $this->wrapper = $wrapper ?: new Wrapper($this->terminal);
    }

    /**
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool         $newline  Whether to add a newline
     * @param int          $options  A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered
     *                               the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    public function write($messages, $newline = false, $options = 0)
    {
        $this->buffer = [];
        $this->output->write($messages, $newline, $options);
    }

    /**
     * @param string|string[] $messages The message as an array of lines or a single string
     * @param bool            $newline  Whether to add a newline
     * @param int             $options  A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered
     *                                  the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    public function reWrite($messages, $newline = false, $options = 0)
    {
        $messages = $this->format($messages, $options);
        if (count($messages) === 0) {
            return;
        }

        $messages = $this->splitNewLines($messages);
        $messages = ($this->trim) ? $this->wrapper->trim($messages) : $this->wrapper->wrap($messages);

        $outputOptions = self::OUTPUT_RAW | $this->output->getVerbosity();

        if (count($this->buffer) === 0) {
            $this->buffer = $messages;
            if ($newline) {
                $this->output->write($messages, true, $outputOptions);
            } else {
                $i = 0;
                $total = count($messages);
                foreach ($messages as $message) {
                    $this->output->write($message, ++$i < $total, $outputOptions);
                }
            }
            return;
        }

        $sizeDiff = ($newline ? 1 : 0);
        if (count($messages) + $sizeDiff > $this->terminal->getHeight()) {
            $messages = array_slice($messages, count($messages) + $sizeDiff - $this->terminal->getHeight());
        }

        $diff = $this->diff->lines($this->buffer, $messages);

        $output = $this->buildOutput($diff, $newline);
        $this->buffer = $messages;

        $this->output->write($output, $newline, $outputOptions);
    }

    /**
     * @param string|string[] $messages The message as an array of lines or a single string
     * @param int             $options  A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered
     *                                  the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     *
     * @return \string[]
     */
    private function format($messages, $options = 0)
    {
        $messages = (array) $messages;

        $types = self::OUTPUT_NORMAL | self::OUTPUT_RAW | self::OUTPUT_PLAIN;
        $type = $types & $options ?: self::OUTPUT_NORMAL;

        $verbosities = self::VERBOSITY_QUIET | self::VERBOSITY_NORMAL | self::VERBOSITY_VERBOSE | self::VERBOSITY_VERY_VERBOSE | self::VERBOSITY_DEBUG;
        $verbosity = $verbosities & $options ?: self::VERBOSITY_NORMAL;

        if ($verbosity > $this->getVerbosity()) {
            return [];
        }

        $formatter = $this->output->getFormatter();

        return array_map(function ($message) use ($type, $formatter) {
            switch ($type) {
                case OutputInterface::OUTPUT_NORMAL:
                    return $formatter->format($message);
                case OutputInterface::OUTPUT_PLAIN:
                    return strip_tags($formatter->format($message));
                case OutputInterface::OUTPUT_RAW:
                default:
                    return $message;
            }
        }, $messages);
    }

    /**
     * @param string|string[] $messages
     *
     * @return string[]
     */
    private function splitNewLines($messages)
    {
        $exploded = array_map(function ($line) {
            return explode("\n", $line);
        }, (array) $messages);
        $out = [];
        array_walk_recursive($exploded, function ($a) use (&$out) {
            $out[] = $a;
        });
        return $out;
    }

    /**
     * @return TerminalInterface
     */
    public function getTerminal()
    {
        return $this->terminal;
    }

    /**
     * @param array $diff
     * @param bool  $newline
     *
     * @return string
     */
    private function buildOutput(array $diff, $newline = false)
    {
        $buffer = '';

        // reset cursor position
        $count = count($this->buffer);
        $mod = ($newline ? 1 : 0);
        $up = ($count > 0 ? $count - 1 + $mod : $mod);
        if ($up > 0) {
            $buffer .= $this->terminal->moveUp($up);
        }
        $buffer .= "\r";

        $diffSize = count($diff);

        for ($i = 0; $i < $diffSize; $i++) {
            $d = $diff[$i];
            if ($i !== 0) {
                $buffer .= PHP_EOL . "\r";
            }
            if (!is_null($d)) {
                if ($d['col'] > 0) {
                    $buffer .= $this->terminal->moveRight($d['col']);
                }
                $buffer .= $this->terminal->eraseToEnd() . $d['str'];
            }
        }

        return $this->terminal->hideCursor() . $buffer . $this->terminal->showCursor();
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string|array $messages The message as an array of lines of a single string
     * @param int          $options  A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered
     *                               the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    public function writeln($messages, $options = 0)
    {
        $this->write($messages, true, $options);
    }

    /**
     * Sets the verbosity of the output.
     *
     * @param int $level The level of verbosity (one of the VERBOSITY constants)
     */
    public function setVerbosity($level)
    {
        $this->output->setVerbosity($level);
    }

    /**
     * Gets the current verbosity of the output.
     *
     * @return int The current level of verbosity (one of the VERBOSITY constants)
     */
    public function getVerbosity()
    {
        return $this->output->getVerbosity();
    }

    /**
     * Returns whether verbosity is quiet (-q).
     *
     * @return bool true if verbosity is set to VERBOSITY_QUIET, false otherwise
     */
    public function isQuiet()
    {
        return $this->output->isQuiet();
    }

    /**
     * Returns whether verbosity is verbose (-v).
     *
     * @return bool true if verbosity is set to VERBOSITY_VERBOSE, false otherwise
     */
    public function isVerbose()
    {
        return $this->output->isVerbose();
    }

    /**
     * Returns whether verbosity is very verbose (-vv).
     *
     * @return bool true if verbosity is set to VERBOSITY_VERY_VERBOSE, false otherwise
     */
    public function isVeryVerbose()
    {
        return $this->output->isVeryVerbose();
    }

    /**
     * Returns whether verbosity is debug (-vvv).
     *
     * @return bool true if verbosity is set to VERBOSITY_DEBUG, false otherwise
     */
    public function isDebug()
    {
        return $this->output->isDebug();
    }

    /**
     * Sets the decorated flag.
     *
     * @param bool $decorated Whether to decorate the messages
     */
    public function setDecorated($decorated)
    {
        $this->output->setDecorated($decorated);
    }

    /**
     * Gets the decorated flag.
     *
     * @return bool true if the output will decorate messages, false otherwise
     */
    public function isDecorated()
    {
        return $this->output->isDecorated();
    }

    /**
     * Sets output formatter.
     *
     * @param OutputFormatterInterface $formatter
     */
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        $this->output->setFormatter($formatter);
    }

    /**
     * Returns current output formatter instance.
     *
     * @return OutputFormatterInterface
     */
    public function getFormatter()
    {
        return $this->output->getFormatter();
    }

    /**
     * @return bool
     */
    public function isTrim()
    {
        return $this->trim;
    }

    /**
     * Should we wrap the input or not, if this is set to false, it will trim each line
     *
     * @param bool $trim
     *
     * @return $this
     */
    public function setTrim($trim)
    {
        $this->trim = $trim;
        return $this;
    }
}

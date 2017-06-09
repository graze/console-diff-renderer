<?php

namespace Graze\BufferedConsole;

use Graze\BufferedConsole\Diff\ConsoleDiff;
use Graze\BufferedConsole\Terminal\Terminal;
use Graze\BufferedConsole\Terminal\TerminalInterface;
use Graze\BufferedConsole\Wrap\Wrapper;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This takes an array of lines to write to the console, does a different and only over-writes what has changed to the
 * console
 */
class BufferedConsoleOutput implements ConsoleOutputInterface
{
    /** @var string[] */
    private $buffer = [];
    /** @var ConsoleDiff */
    private $diff;
    /** @var TerminalInterface */
    private $terminal;
    /** @var ConsoleOutputInterface */
    private $output;
    /** @var Wrapper */
    private $wrapper;
    /** @var bool */
    private $trim = false;

    /**
     * Constructor.
     *
     * @param ConsoleOutputInterface $output
     * @param TerminalInterface      $terminal
     * @param Wrapper                $wrapper
     */
    public function __construct(
        ConsoleOutputInterface $output,
        TerminalInterface $terminal = null,
        Wrapper $wrapper = null
    ) {
        $this->output = $output;
        $this->diff = new ConsoleDiff();
        $this->terminal = $terminal ?: new Terminal();
        $this->wrapper = $wrapper ?: new Wrapper($this->terminal->getWidth());
    }

    /**
     * Sets information about the terminal
     *
     * @param TerminalInterface $terminal
     */
    public function setTerminal(TerminalInterface $terminal)
    {
        $this->terminal = $terminal;
        $this->wrapper->setWidth($terminal->getWidth());
    }

    /**
     * @return TerminalInterface
     */
    public function getTerminal()
    {
        return $this->terminal;
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
        $messages = (array) $messages;
        $messages = ($this->trim) ? $this->wrapper->trim($messages) : $this->wrapper->wrap($messages);

        if (count($this->buffer) === 0) {
            $this->buffer = $messages;
            $this->output->write($messages, $newline, $options);
            return;
        }

        $sizeDiff = ($newline ? 1 : 0);
        if (count($messages) + $sizeDiff > $this->terminal->getHeight()) {
            $messages = array_slice($messages, count($messages) + $sizeDiff - $this->terminal->getHeight());
        }

        $diff = $this->diff->lines($this->buffer, $messages);

        $output = $this->buildOutput($diff, $newline);
        $this->buffer = $messages;

        $this->output->write($output, $newline, $options);
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
                $buffer .= PHP_EOL;
            }
            if (!is_null($d)) {
                if ($d['col'] > 0) {
                    $buffer .= $this->terminal->moveRight($d['col']);
                }
                $buffer .= $this->terminal->eraseToEnd() . $d['str'];
            }
        }

        return $buffer;
    }

    /**
     * Gets the OutputInterface for errors.
     *
     * @return OutputInterface
     */
    public function getErrorOutput()
    {
        return $this->output->getErrorOutput();
    }

    /**
     * Sets the OutputInterface used for errors.
     *
     * @param OutputInterface $error
     */
    public function setErrorOutput(OutputInterface $error)
    {
        $this->output->setErrorOutput($error);
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

<?php

namespace Graze\BufferedConsole;

use Graze\BufferedConsole\Cursor\ANSI;
use Graze\BufferedConsole\Cursor\CursorInterface;
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
    /** @var FirstDiff */
    private $diff;
    /** @var CursorInterface */
    private $cursor;
    /** @var ConsoleOutputInterface */
    private $output;

    /**
     * Constructor.
     *
     * @param ConsoleOutputInterface $output
     * @param CursorInterface        $cursor
     */
    public function __construct(
        ConsoleOutputInterface $output,
        CursorInterface $cursor = null
    ) {
        $this->output = $output;
        $this->diff = new FirstDiff();
        $this->cursor = $cursor ?: new ANSI();
    }

    /**
     * Sets the cursor to use when navigating around the terminal
     *
     * @param CursorInterface $cursor
     */
    public function setCursor(CursorInterface $cursor)
    {
        $this->cursor = $cursor;
    }

    /**
     * @return CursorInterface
     */
    public function getCursor()
    {
        return $this->cursor;
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
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool         $newline  Whether to add a newline
     * @param int          $options  A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered
     *                               the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     */
    public function reWrite($messages, $newline = false, $options = 0)
    {
        $messages = (array) $messages;

        if (count($this->buffer) === 0) {
            $this->buffer = $messages;
            $this->output->write($messages, $newline, $options);
            return;
        }

        $diff = $this->diff->lines($this->buffer, $messages);

        // replace col number with strip_tags version to represent what is outputted to the user
        for ($i = 0; $i < count($messages); $i++) {
            if (isset($diff[$i]) && !is_null($messages[$i]) && $diff[$i]['col'] > 0) {
                $diff[$i]['col'] = mb_strlen(strip_tags(mb_substr($messages[$i], 0, $diff[$i]['col'])));
            }
        }

        $buffer = '';

        // reset cursor position
        $count = count($this->buffer);
        $mod = ($newline ? 0 : 1);
        $up = ($count > 0 ? $count - $mod : 0);
        if ($up > 0) {
            $buffer .= $this->cursor->moveUp($up);
        }
        $buffer .= "\r";

        for ($i = 0; $i < count($diff); $i++) {
            $d = $diff[$i];
            if ($i !== 0) {
                $buffer .= PHP_EOL;
            }
            if (!is_null($d)) {
                if ($d['col'] > 0) {
                    $buffer .= $this->cursor->moveRight($d['col']);
                }
                $buffer .= $this->cursor->eraseToEnd() . $d['str'];
            }
        }

        $this->buffer = $messages;

        $this->output->write($buffer, $newline, $options);
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
}

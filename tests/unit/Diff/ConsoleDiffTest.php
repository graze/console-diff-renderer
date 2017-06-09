<?php
/**
 * This file is part of graze/buffered-console.
 *
 * Copyright (c) 2017 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license https://github.com/graze/buffered-console/blob/master/LICENSE.md
 * @link    https://github.com/graze/buffered-console
 */

namespace Graze\BufferedConsole\Test\Unit\Diff;

use Graze\BufferedConsole\Diff\ConsoleDiff;
use Graze\BufferedConsole\Test\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleDiffTest extends TestCase
{
    /**
     * @dataProvider differenceWithTagsData
     *
     * @param string[] $old
     * @param string[] $new
     * @param int      $options
     * @param string[] $expected
     */
    public function testDifferenceWithTags(array $old, array $new, $options, array $expected)
    {
        $diff = new ConsoleDiff();

        $this->assertEquals($expected, $diff->lines($old, $new, $options));
    }

    /**
     * @return array
     */
    public function differenceWithTagsData()
    {
        return [
            [ // col now equals the strip_tags version (hence 6)
                ['<info>first</info>', '<info>second</info>'],
                ['<info>new</info>', '<info>second cake</info>'],
                OutputInterface::OUTPUT_NORMAL,
                [
                    ['col' => 0, 'str' => '<info>new</info>'],
                    ['col' => 6, 'str' => '<info> cake</info>'],
                ],
            ],
            [ // multiple tags should represent all tags
                ['<info><error>first</error></info>', '<info>second</info>'],
                ['<info><error>new</error></info>', '<info>second cake</info>'],
                OutputInterface::OUTPUT_NORMAL,
                [
                    ['col' => 0, 'str' => '<info><error>new</error></info>'],
                    ['col' => 6, 'str' => '<info> cake</info>'],
                ],
            ],
            [
                ['<info><error>first</error></info>', '<info>second</info>'],
                ['<info><error>new</error></info>', '<info>second cake</info>'],
                OutputInterface::OUTPUT_RAW,
                [
                    ['col' => 13, 'str' => 'new</error></info>'],
                    ['col' => 12, 'str' => ' cake</info>'],
                ],
            ],
            [ // support </> as a closing tag for any tag
                ['<info><fg=bla;bg=bar>first</>', '<info>second</info>'],
                ['<info><fg=bla;bg=bar>first</> cake', '<info>second cake</info>'],
                OutputInterface::OUTPUT_NORMAL,
                [
                    ['col' => 5, 'str' => ' cake'],
                    ['col' => 6, 'str' => '<info> cake</info>'],
                ],
            ],
        ];
    }
}

<?php

namespace Graze\BufferedConsole\Diff;

use Symfony\Component\Console\Output\OutputInterface;

class ConsoleDiff extends FirstDiff
{
    const UNCLOSED_TAGS = '/<(?P<tag>[a-z;=]+)>(?!.*?<\/(?:(?P=tag)|)>)/i';

    /** @var FirstDiff */
    private $diff;

    /**
     * ConsoleDiff constructor.
     *
     * @param FirstDiff|null $differ
     */
    public function __construct($differ = null)
    {
        $this->diff = $differ ?: new FirstDiff();
    }

    /**
     * @param string[] $old
     * @param string[] $new
     * @param int      $options
     *
     * @return string[]
     */
    public function lines(array $old, array $new, $options = OutputInterface::OUTPUT_NORMAL)
    {
        $diff = $this->diff->lines($old, $new);

        if (($options & OutputInterface::OUTPUT_NORMAL) == OutputInterface::OUTPUT_NORMAL) {
            // replace col number with strip_tags version to represent what is outputted to the user
            $len = count($new);
            for ($i = 0; $i < $len; $i++) {
                if (isset($diff[$i]) && !is_null($new[$i]) && $diff[$i]['col'] > 0) {
                    $tags = $this->getUnclosedTags(mb_substr($new[$i], 0, $diff[$i]['col']));
                    if (count($tags) > 0) {
                        $diff[$i]['str'] = '<' . implode('><', $tags) . '>' . $diff[$i]['str'];
                    }
                    $diff[$i]['col'] = mb_strlen(strip_tags(mb_substr($new[$i], 0, $diff[$i]['col'])));
                }
            }
        }

        return $diff;
    }

    /**
     * Find a list of unclosed tags
     *
     * @param string $string
     *
     * @return string[]
     */
    private function getUnclosedTags($string)
    {
        if (preg_match_all(static::UNCLOSED_TAGS, $string, $matches)) {
            return $matches['tag'];
        }
        return [];
    }
}

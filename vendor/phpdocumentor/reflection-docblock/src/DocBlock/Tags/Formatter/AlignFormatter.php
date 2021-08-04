<?php

declare (strict_types=1);
/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link      http://phpdoc.org
 */
namespace ECSPrefix20210804\phpDocumentor\Reflection\DocBlock\Tags\Formatter;

use ECSPrefix20210804\phpDocumentor\Reflection\DocBlock\Tag;
use ECSPrefix20210804\phpDocumentor\Reflection\DocBlock\Tags\Formatter;
use function max;
use function str_repeat;
use function strlen;
class AlignFormatter implements \ECSPrefix20210804\phpDocumentor\Reflection\DocBlock\Tags\Formatter
{
    /** @var int The maximum tag name length. */
    protected $maxLen = 0;
    /**
     * @param Tag[] $tags All tags that should later be aligned with the formatter.
     */
    public function __construct(array $tags)
    {
        foreach ($tags as $tag) {
            $this->maxLen = \max($this->maxLen, \strlen($tag->getName()));
        }
    }
    /**
     * Formats the given tag to return a simple plain text version.
     */
    public function format(\ECSPrefix20210804\phpDocumentor\Reflection\DocBlock\Tag $tag) : string
    {
        return '@' . $tag->getName() . \str_repeat(' ', $this->maxLen - \strlen($tag->getName()) + 1) . $tag;
    }
}
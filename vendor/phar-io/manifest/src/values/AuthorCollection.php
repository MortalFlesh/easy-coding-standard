<?php

declare (strict_types=1);
/*
 * This file is part of PharIo\Manifest.
 *
 * (c) Arne Blankerts <arne@blankerts.de>, Sebastian Heuer <sebastian@phpeople.de>, Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ECSPrefix20210804\PharIo\Manifest;

class AuthorCollection implements \Countable, \IteratorAggregate
{
    /** @var Author[] */
    private $authors = [];
    public function add(\ECSPrefix20210804\PharIo\Manifest\Author $author) : void
    {
        $this->authors[] = $author;
    }
    /**
     * @return Author[]
     */
    public function getAuthors() : array
    {
        return $this->authors;
    }
    public function count() : int
    {
        return \count($this->authors);
    }
    public function getIterator() : \ECSPrefix20210804\PharIo\Manifest\AuthorCollectionIterator
    {
        return new \ECSPrefix20210804\PharIo\Manifest\AuthorCollectionIterator($this);
    }
}
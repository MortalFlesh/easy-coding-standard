<?php

declare (strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ECSPrefix20210804\PHPUnit\Framework\MockObject;

use function sprintf;
/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DuplicateMethodException extends \ECSPrefix20210804\PHPUnit\Framework\Exception implements \ECSPrefix20210804\PHPUnit\Framework\MockObject\Exception
{
    /**
     * @psalm-param list<string> $methods
     */
    public function __construct(array $methods)
    {
        parent::__construct(\sprintf('Cannot stub or mock using a method list that contains duplicates: "%s" (duplicate: "%s")', \implode(', ', $methods), \implode(', ', \array_unique(\array_diff_assoc($methods, \array_unique($methods))))));
    }
}
<?php

declare (strict_types=1);
/*
 * This file is part of phpunit/php-code-coverage.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ECSPrefix20210804\SebastianBergmann\CodeCoverage\Driver;

use RuntimeException;
use ECSPrefix20210804\SebastianBergmann\CodeCoverage\Exception;
final class PhpdbgNotAvailableException extends \RuntimeException implements \ECSPrefix20210804\SebastianBergmann\CodeCoverage\Exception
{
    public function __construct()
    {
        parent::__construct('The PHPDBG SAPI is not available');
    }
}
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
namespace ECSPrefix20210804\PHPUnit\Util\TestDox;

use ECSPrefix20210804\PHPUnit\Framework\TestResult;
/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TextResultPrinter extends \ECSPrefix20210804\PHPUnit\Util\TestDox\ResultPrinter
{
    public function printResult(\ECSPrefix20210804\PHPUnit\Framework\TestResult $result) : void
    {
    }
    /**
     * Handler for 'start class' event.
     */
    protected function startClass(string $name) : void
    {
        $this->write($this->currentTestClassPrettified . "\n");
    }
    /**
     * Handler for 'on test' event.
     */
    protected function onTest(string $name, bool $success = \true) : void
    {
        if ($success) {
            $this->write(' [x] ');
        } else {
            $this->write(' [ ] ');
        }
        $this->write($name . "\n");
    }
    /**
     * Handler for 'end class' event.
     */
    protected function endClass(string $name) : void
    {
        $this->write("\n");
    }
}
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
namespace ECSPrefix20210804\PHPUnit\TextUI\XmlConfiguration;

use DOMDocument;
use DOMElement;
use ECSPrefix20210804\PHPUnit\Util\Xml\SnapshotNodeList;
/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class RemoveLogTypes implements \ECSPrefix20210804\PHPUnit\TextUI\XmlConfiguration\Migration
{
    public function migrate(\DOMDocument $document) : void
    {
        $logging = $document->getElementsByTagName('logging')->item(0);
        if (!$logging instanceof \DOMElement) {
            return;
        }
        foreach (\ECSPrefix20210804\PHPUnit\Util\Xml\SnapshotNodeList::fromNodeList($logging->getElementsByTagName('log')) as $logNode) {
            switch ($logNode->getAttribute('type')) {
                case 'json':
                case 'tap':
                    $logging->removeChild($logNode);
            }
        }
    }
}
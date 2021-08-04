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
final class MoveWhitelistExcludesToCoverage implements \ECSPrefix20210804\PHPUnit\TextUI\XmlConfiguration\Migration
{
    /**
     * @throws MigrationException
     */
    public function migrate(\DOMDocument $document) : void
    {
        $whitelist = $document->getElementsByTagName('whitelist')->item(0);
        if ($whitelist === null) {
            return;
        }
        $excludeNodes = \ECSPrefix20210804\PHPUnit\Util\Xml\SnapshotNodeList::fromNodeList($whitelist->getElementsByTagName('exclude'));
        if ($excludeNodes->count() === 0) {
            return;
        }
        $coverage = $document->getElementsByTagName('coverage')->item(0);
        if (!$coverage instanceof \DOMElement) {
            throw new \ECSPrefix20210804\PHPUnit\TextUI\XmlConfiguration\MigrationException('Unexpected state - No coverage element');
        }
        $targetExclude = $coverage->getElementsByTagName('exclude')->item(0);
        if ($targetExclude === null) {
            $targetExclude = $coverage->appendChild($document->createElement('exclude'));
        }
        foreach ($excludeNodes as $excludeNode) {
            \assert($excludeNode instanceof \DOMElement);
            foreach (\ECSPrefix20210804\PHPUnit\Util\Xml\SnapshotNodeList::fromNodeList($excludeNode->childNodes) as $child) {
                if (!$child instanceof \DOMElement || !\in_array($child->nodeName, ['directory', 'file'], \true)) {
                    continue;
                }
                $targetExclude->appendChild($child);
            }
            if ($excludeNode->getElementsByTagName('*')->count() !== 0) {
                throw new \ECSPrefix20210804\PHPUnit\TextUI\XmlConfiguration\MigrationException('Dangling child elements in exclude found.');
            }
            $whitelist->removeChild($excludeNode);
        }
    }
}
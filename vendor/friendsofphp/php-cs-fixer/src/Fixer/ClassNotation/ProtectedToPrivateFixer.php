<?php

declare (strict_types=1);
/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace PhpCsFixer\Fixer\ClassNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;
/**
 * @author Filippo Tessarotto <zoeslam@gmail.com>
 */
final class ProtectedToPrivateFixer extends \PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition() : \PhpCsFixer\FixerDefinition\FixerDefinitionInterface
    {
        return new \PhpCsFixer\FixerDefinition\FixerDefinition('Converts `protected` variables and methods to `private` where possible.', [new \PhpCsFixer\FixerDefinition\CodeSample('<?php
final class Sample
{
    protected $a;

    protected function test()
    {
    }
}
')]);
    }
    /**
     * {@inheritdoc}
     *
     * Must run before OrderedClassElementsFixer.
     * Must run after FinalInternalClassFixer.
     */
    public function getPriority() : int
    {
        return 66;
    }
    /**
     * {@inheritdoc}
     */
    public function isCandidate(\PhpCsFixer\Tokenizer\Tokens $tokens) : bool
    {
        if (\defined('T_ENUM') && $tokens->isAllTokenKindsFound([T_ENUM, \T_PROTECTED])) {
            // @TODO: drop condition when PHP 8.1+ is required
            return \true;
        }
        return $tokens->isAllTokenKindsFound([\T_CLASS, \T_FINAL, \T_PROTECTED]);
    }
    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, \PhpCsFixer\Tokenizer\Tokens $tokens) : void
    {
        $tokensAnalyzer = new \PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
        $modifierKinds = [\T_PUBLIC, \T_PROTECTED, \T_PRIVATE, \T_FINAL, \T_ABSTRACT, \T_NS_SEPARATOR, \T_STRING, \PhpCsFixer\Tokenizer\CT::T_NULLABLE_TYPE, \PhpCsFixer\Tokenizer\CT::T_ARRAY_TYPEHINT, \T_STATIC, \PhpCsFixer\Tokenizer\CT::T_TYPE_ALTERNATION, \PhpCsFixer\Tokenizer\CT::T_TYPE_INTERSECTION];
        if (\defined('T_READONLY')) {
            // @TODO: drop condition when PHP 8.1+ is required
            $modifierKinds[] = T_READONLY;
        }
        $classesCandidate = [];
        $classElementTypes = ['method' => \true, 'property' => \true, 'const' => \true];
        foreach ($tokensAnalyzer->getClassyElements() as $index => $element) {
            $classIndex = $element['classIndex'];
            if (!\array_key_exists($classIndex, $classesCandidate)) {
                $classesCandidate[$classIndex] = $this->isClassCandidate($tokens, $classIndex);
            }
            if (\false === $classesCandidate[$classIndex]) {
                continue;
                // not "final" class, "extends", is "anonymous", enum or uses trait
            }
            if (!isset($classElementTypes[$element['type']])) {
                continue;
            }
            $previous = $index;
            $isProtected = \false;
            $isFinal = \false;
            do {
                $previous = $tokens->getPrevMeaningfulToken($previous);
                if ($tokens[$previous]->isGivenKind(\T_PROTECTED)) {
                    $isProtected = $previous;
                } elseif ($tokens[$previous]->isGivenKind(\T_FINAL)) {
                    $isFinal = $previous;
                }
            } while ($tokens[$previous]->isGivenKind($modifierKinds));
            if (\false === $isProtected) {
                continue;
            }
            if ($isFinal && 'const' === $element['type']) {
                continue;
                // Final constants cannot be private
            }
            $element['protected_index'] = $isProtected;
            $tokens[$element['protected_index']] = new \PhpCsFixer\Tokenizer\Token([\T_PRIVATE, 'private']);
        }
    }
    private function isClassCandidate(\PhpCsFixer\Tokenizer\Tokens $tokens, int $classIndex) : bool
    {
        if (\defined('T_ENUM') && $tokens[$classIndex]->isGivenKind(T_ENUM)) {
            // @TODO: drop condition when PHP 8.1+ is required
            return \true;
        }
        $prevToken = $tokens[$tokens->getPrevMeaningfulToken($classIndex)];
        if (!$prevToken->isGivenKind(\T_FINAL)) {
            return \false;
        }
        $classNameIndex = $tokens->getNextMeaningfulToken($classIndex);
        // move to class name as anonymous class is never "final"
        $classExtendsIndex = $tokens->getNextMeaningfulToken($classNameIndex);
        // move to possible "extends"
        if ($tokens[$classExtendsIndex]->isGivenKind(\T_EXTENDS)) {
            return \false;
        }
        if (!$tokens->isTokenKindFound(\PhpCsFixer\Tokenizer\CT::T_USE_TRAIT)) {
            return \true;
            // cheap test
        }
        $classOpenIndex = $tokens->getNextTokenOfKind($classNameIndex, ['{']);
        $classCloseIndex = $tokens->findBlockEnd(\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_CURLY_BRACE, $classOpenIndex);
        $useIndex = $tokens->getNextTokenOfKind($classOpenIndex, [[\PhpCsFixer\Tokenizer\CT::T_USE_TRAIT]]);
        return null === $useIndex || $useIndex > $classCloseIndex;
    }
}

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
namespace PhpCsFixer\Linter;

use PhpCsFixer\FileReader;
use PhpCsFixer\Tokenizer\CodeHasher;
use PhpCsFixer\Tokenizer\Tokens;
/**
 * Handle PHP code linting.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 */
final class TokenizerLinter implements \PhpCsFixer\Linter\LinterInterface
{
    /**
     * {@inheritdoc}
     */
    public function isAsync() : bool
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     */
    public function lintFile(string $path) : \PhpCsFixer\Linter\LintingResultInterface
    {
        return $this->lintSource(\PhpCsFixer\FileReader::createSingleton()->read($path));
    }
    /**
     * {@inheritdoc}
     */
    public function lintSource(string $source) : \PhpCsFixer\Linter\LintingResultInterface
    {
        try {
            // To lint, we will parse the source into Tokens.
            // During that process, it might throw a ParseError or CompileError.
            // If it won't, cache of tokenized version of source will be kept, which is great for Runner.
            // Yet, first we need to clear already existing cache to not hit it and lint the code indeed.
            $codeHash = \PhpCsFixer\Tokenizer\CodeHasher::calculateCodeHash($source);
            \PhpCsFixer\Tokenizer\Tokens::clearCache($codeHash);
            \PhpCsFixer\Tokenizer\Tokens::fromCode($source);
            return new \PhpCsFixer\Linter\TokenizerLintingResult();
        } catch (\ParseError $e) {
            return new \PhpCsFixer\Linter\TokenizerLintingResult($e);
        } catch (\CompileError $e) {
            return new \PhpCsFixer\Linter\TokenizerLintingResult($e);
        }
    }
}

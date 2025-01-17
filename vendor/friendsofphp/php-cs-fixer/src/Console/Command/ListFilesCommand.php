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
namespace PhpCsFixer\Console\Command;

use PhpCsFixer\Config;
use PhpCsFixer\ConfigInterface;
use PhpCsFixer\Console\ConfigurationResolver;
use PhpCsFixer\ToolInfoInterface;
use SplFileInfo;
use ECSPrefix20220403\Symfony\Component\Console\Command\Command;
use ECSPrefix20220403\Symfony\Component\Console\Input\InputInterface;
use ECSPrefix20220403\Symfony\Component\Console\Input\InputOption;
use ECSPrefix20220403\Symfony\Component\Console\Output\OutputInterface;
/**
 * @author Markus Staab <markus.staab@redaxo.org>
 *
 * @internal
 */
final class ListFilesCommand extends \ECSPrefix20220403\Symfony\Component\Console\Command\Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'list-files';
    /**
     * @var \PhpCsFixer\ConfigInterface
     */
    private $defaultConfig;
    /**
     * @var \PhpCsFixer\ToolInfoInterface
     */
    private $toolInfo;
    public function __construct(\PhpCsFixer\ToolInfoInterface $toolInfo)
    {
        parent::__construct();
        $this->defaultConfig = new \PhpCsFixer\Config();
        $this->toolInfo = $toolInfo;
    }
    /**
     * {@inheritdoc}
     */
    protected function configure() : void
    {
        $this->setDefinition([new \ECSPrefix20220403\Symfony\Component\Console\Input\InputOption('config', '', \ECSPrefix20220403\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'The path to a .php-cs-fixer.php file.')])->setDescription('List all files being fixed by the given config.');
    }
    protected function execute(\ECSPrefix20220403\Symfony\Component\Console\Input\InputInterface $input, \ECSPrefix20220403\Symfony\Component\Console\Output\OutputInterface $output) : int
    {
        $passedConfig = $input->getOption('config');
        $cwd = \getcwd();
        $resolver = new \PhpCsFixer\Console\ConfigurationResolver($this->defaultConfig, ['config' => $passedConfig], $cwd, $this->toolInfo);
        $finder = $resolver->getFinder();
        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            if ($file->isFile()) {
                $relativePath = \str_replace($cwd, '.', $file->getRealPath());
                // unify directory separators across operating system
                $relativePath = \str_replace('/', \DIRECTORY_SEPARATOR, $relativePath);
                $output->writeln(\escapeshellarg($relativePath));
            }
        }
        return 0;
    }
}

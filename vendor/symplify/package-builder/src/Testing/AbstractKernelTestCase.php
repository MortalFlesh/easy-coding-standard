<?php

declare (strict_types=1);
namespace ECSPrefix20220403\Symplify\PackageBuilder\Testing;

use ECSPrefix20220403\PHPUnit\Framework\TestCase;
use ReflectionClass;
use ECSPrefix20220403\Symfony\Component\Console\Output\OutputInterface;
use ECSPrefix20220403\Symfony\Component\Console\Style\SymfonyStyle;
use ECSPrefix20220403\Symfony\Component\DependencyInjection\ContainerInterface;
use ECSPrefix20220403\Symfony\Component\HttpKernel\KernelInterface;
use ECSPrefix20220403\Symfony\Contracts\Service\ResetInterface;
use ECSPrefix20220403\Symplify\PackageBuilder\Contract\HttpKernel\ExtraConfigAwareKernelInterface;
use ECSPrefix20220403\Symplify\PackageBuilder\Exception\HttpKernel\MissingInterfaceException;
use ECSPrefix20220403\Symplify\SmartFileSystem\SmartFileInfo;
use ECSPrefix20220403\Symplify\SymplifyKernel\Contract\LightKernelInterface;
use ECSPrefix20220403\Symplify\SymplifyKernel\Exception\ShouldNotHappenException;
/**
 * Inspiration
 *
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Bundle/FrameworkBundle/Test/KernelTestCase.php
 */
abstract class AbstractKernelTestCase extends \ECSPrefix20220403\PHPUnit\Framework\TestCase
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface|\Symplify\SymplifyKernel\Contract\LightKernelInterface|null
     */
    protected static $kernel = null;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface|null
     */
    protected static $container;
    /**
     * @param class-string<KernelInterface|LightKernelInterface> $kernelClass
     * @param string[]|SmartFileInfo[] $configs
     * @return \Symfony\Component\HttpKernel\KernelInterface|\Symplify\SymplifyKernel\Contract\LightKernelInterface
     */
    protected function bootKernelWithConfigs(string $kernelClass, array $configs)
    {
        // unwrap file infos to real paths
        $configFilePaths = $this->resolveConfigFilePaths($configs);
        $configsHash = $this->resolveConfigsHash($configFilePaths);
        $this->ensureKernelShutdown();
        $bootedKernel = $this->createBootedKernelFromConfigs($kernelClass, $configsHash, $configFilePaths);
        static::$kernel = $bootedKernel;
        self::$container = $bootedKernel->getContainer();
        return $bootedKernel;
    }
    /**
     * Syntax sugger to remove static from the test cases vision
     *
     * @template T of object
     * @param class-string<T> $type
     * @return object
     */
    protected function getService(string $type)
    {
        if (self::$container === null) {
            throw new \ECSPrefix20220403\Symplify\SymplifyKernel\Exception\ShouldNotHappenException('First, create container with booKernel(KernelClass::class)');
        }
        $service = self::$container->get($type);
        if ($service === null) {
            $errorMessage = \sprintf('Services "%s" was not found', $type);
            throw new \ECSPrefix20220403\Symplify\Astral\Exception\ShouldNotHappenException($errorMessage);
        }
        return $service;
    }
    /**
     * @param class-string<KernelInterface|LightKernelInterface> $kernelClass
     */
    protected function bootKernel(string $kernelClass) : void
    {
        if (\is_a($kernelClass, \ECSPrefix20220403\Symplify\SymplifyKernel\Contract\LightKernelInterface::class, \true)) {
            /** @var LightKernelInterface $kernel */
            $kernel = new $kernelClass();
            $kernel->createFromConfigs([]);
            static::$kernel = $kernel;
            self::$container = $kernel->getContainer();
            return;
        }
        $this->ensureKernelShutdown();
        $kernel = new $kernelClass('test', \true);
        if (!$kernel instanceof \ECSPrefix20220403\Symfony\Component\HttpKernel\KernelInterface) {
            throw new \ECSPrefix20220403\Symplify\SymplifyKernel\Exception\ShouldNotHappenException();
        }
        static::$kernel = $this->bootAndReturnKernel($kernel);
    }
    /**
     * Shuts the kernel down if it was used in the test.
     */
    protected function ensureKernelShutdown() : void
    {
        if (static::$kernel !== null && static::$kernel instanceof \ECSPrefix20220403\Symfony\Component\HttpKernel\KernelInterface) {
            // make sure boot() is called
            // @see https://github.com/symfony/symfony/pull/31202/files
            $kernelReflectionClass = new \ReflectionClass(static::$kernel);
            $containerReflectionProperty = $kernelReflectionClass->getProperty('container');
            $containerReflectionProperty->setAccessible(\true);
            $kernel = $containerReflectionProperty->getValue(static::$kernel);
            if ($kernel !== null) {
                $container = static::$kernel->getContainer();
                static::$kernel->shutdown();
                if ($container instanceof \ECSPrefix20220403\Symfony\Contracts\Service\ResetInterface) {
                    $container->reset();
                }
            }
        }
        static::$container = null;
    }
    /**
     * @param string[] $configs
     */
    protected function resolveConfigsHash(array $configs) : string
    {
        $configsHash = '';
        foreach ($configs as $config) {
            $configsHash .= \md5_file($config);
        }
        return \md5($configsHash);
    }
    /**
     * @param string[]|SmartFileInfo[] $configs
     * @return string[]
     */
    protected function resolveConfigFilePaths(array $configs) : array
    {
        $configFilePaths = [];
        foreach ($configs as $config) {
            $configFilePaths[] = $config instanceof \ECSPrefix20220403\Symplify\SmartFileSystem\SmartFileInfo ? $config->getRealPath() : $config;
        }
        return $configFilePaths;
    }
    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface|\Symplify\SymplifyKernel\Contract\LightKernelInterface $kernel
     */
    private function ensureIsConfigAwareKernel($kernel) : void
    {
        if ($kernel instanceof \ECSPrefix20220403\Symplify\SymplifyKernel\Contract\LightKernelInterface) {
            return;
        }
        if ($kernel instanceof \ECSPrefix20220403\Symplify\PackageBuilder\Contract\HttpKernel\ExtraConfigAwareKernelInterface) {
            return;
        }
        throw new \ECSPrefix20220403\Symplify\PackageBuilder\Exception\HttpKernel\MissingInterfaceException(\sprintf('"%s" is missing an "%s" interface', \get_class($kernel), \ECSPrefix20220403\Symplify\PackageBuilder\Contract\HttpKernel\ExtraConfigAwareKernelInterface::class));
    }
    private function bootAndReturnKernel(\ECSPrefix20220403\Symfony\Component\HttpKernel\KernelInterface $kernel) : \ECSPrefix20220403\Symfony\Component\HttpKernel\KernelInterface
    {
        $kernel->boot();
        $container = $kernel->getContainer();
        // private → public service hack?
        if ($container->has('test.service_container')) {
            $container = $container->get('test.service_container');
        }
        if (!$container instanceof \ECSPrefix20220403\Symfony\Component\DependencyInjection\ContainerInterface) {
            throw new \ECSPrefix20220403\Symplify\SymplifyKernel\Exception\ShouldNotHappenException();
        }
        // has output? keep it silent out of tests
        if ($container->has(\ECSPrefix20220403\Symfony\Component\Console\Style\SymfonyStyle::class)) {
            $symfonyStyle = $container->get(\ECSPrefix20220403\Symfony\Component\Console\Style\SymfonyStyle::class);
            $symfonyStyle->setVerbosity(\ECSPrefix20220403\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_QUIET);
        }
        static::$container = $container;
        return $kernel;
    }
    /**
     * @param class-string<KernelInterface|LightKernelInterface> $kernelClass
     * @param string[] $configFilePaths
     * @return \Symfony\Component\HttpKernel\KernelInterface|\Symplify\SymplifyKernel\Contract\LightKernelInterface
     */
    private function createBootedKernelFromConfigs(string $kernelClass, string $configsHash, array $configFilePaths)
    {
        if (\is_a($kernelClass, \ECSPrefix20220403\Symplify\SymplifyKernel\Contract\LightKernelInterface::class, \true)) {
            /** @var LightKernelInterface $kernel */
            $kernel = new $kernelClass();
            $kernel->createFromConfigs($configFilePaths);
            return $kernel;
        }
        $kernel = new $kernelClass('test_' . $configsHash, \true);
        $this->ensureIsConfigAwareKernel($kernel);
        /** @var ExtraConfigAwareKernelInterface $kernel */
        $kernel->setConfigs($configFilePaths);
        return $this->bootAndReturnKernel($kernel);
    }
}

<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ECSPrefix20210507\Symfony\Component\DependencyInjection\Loader\Configurator;

use ECSPrefix20210507\Symfony\Component\DependencyInjection\ContainerBuilder;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class ParametersConfigurator extends \ECSPrefix20210507\Symfony\Component\DependencyInjection\Loader\Configurator\AbstractConfigurator
{
    const FACTORY = 'parameters';
    private $container;
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }
    /**
     * Creates a parameter.
     *
     * @return $this
     * @param string $name
     */
    public final function set($name, $value)
    {
        $this->container->setParameter($name, static::processValue($value, \true));
        return $this;
    }
    /**
     * Creates a parameter.
     *
     * @return $this
     * @param string $name
     */
    public final function __invoke($name, $value)
    {
        return $this->set($name, $value);
    }
}
<?php

declare (strict_types=1);
namespace ECSPrefix20220202;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use ECSPrefix20220202\Symplify\SmartFileSystem\SmartFileSystem;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\ECSPrefix20220202\Symplify\SmartFileSystem\SmartFileSystem::class);
};
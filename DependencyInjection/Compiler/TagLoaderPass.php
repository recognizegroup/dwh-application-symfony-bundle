<?php

namespace Recognize\DwhApplication\DependencyInjection\Compiler;


use Recognize\DwhApplication\Loader\EntityLoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class TagLoaderPass
 * @package Recognize\DwhApplication\DependencyInjection\Compiler
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
class TagLoaderPass implements CompilerPassInterface
{

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(EntityLoaderInterface::class)
            ->addTag('recognize.dwh_loader');
    }
}

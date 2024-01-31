<?php
namespace Recognize\DwhApplication;

use Recognize\DwhApplication\DependencyInjection\Compiler\TagLoaderPass;
use Recognize\DwhApplication\DependencyInjection\DwhApplicationExtension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class RecognizeDwhApplicationBundle
 * @package Recognize\DwhApplication
 */
class RecognizeDwhApplicationBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TagLoaderPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 100000);
    }

    /**
     * @return DwhApplicationExtension|ExtensionInterface|null
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new DwhApplicationExtension();
    }
}

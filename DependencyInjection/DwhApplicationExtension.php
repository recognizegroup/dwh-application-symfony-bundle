<?php
namespace Recognize\DwhApplication\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class DwhApplicationExtension
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
class DwhApplicationExtension extends Extension
{
    public const ALIAS = 'recognize_dwh';

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('recognize.dwh_application.encryption', $config['encryption']);
        $container->setParameter('recognize.dwh_application.encrypted_token', $config['encrypted_token']);
        $container->setParameter('recognize.dwh_application.protocol_version', $config['protocol_version']);
        $container->setParameter('recognize.dwh_application.specification_version', $config['specification_version']);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return self::ALIAS;
    }
}

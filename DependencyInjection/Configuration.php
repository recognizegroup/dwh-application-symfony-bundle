<?php

namespace Recognize\DwhApplication\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @author Bart Wesselink <b.wesselink@recognize.nl>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(DwhApplicationExtension::ALIAS);

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('path')->defaultValue('/api/dwh')->end()
            ->enumNode('encryption')->values(['bcrypt'])->end()
            ->scalarNode('encrypted_token')->isRequired()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

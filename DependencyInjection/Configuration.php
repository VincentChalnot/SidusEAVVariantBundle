<?php

namespace Sidus\EAVVariantBundle\DependencyInjection;

use Sidus\EAVModelBundle\DependencyInjection\Configuration as BaseConfiguration;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration extends BaseConfiguration
{
    /**
     * {@inheritdoc}
     * @throws \RuntimeException
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sidus_eav_variant');
        $familyDefinition = $rootNode
            ->children()
            ->arrayNode('routes')
            ->isRequired()
            ->children()
            ->scalarNode('select')->isRequired()->end()
            ->scalarNode('create')->isRequired()->end()
            ->scalarNode('edit')->isRequired()->end()
            ->scalarNode('delete')->isRequired()->end()
            ->end()
            ->end()
            ->arrayNode('families')
            ->prototype('array')
            ->children();

        $this->appendFamilyDefinition($familyDefinition);

        $familyDefinition->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }

    protected function appendFamilyDefinition(NodeBuilder $familyDefinition)
    {
        parent::appendFamilyDefinition($familyDefinition);
        $familyDefinition
            ->arrayNode('axles')
            ->prototype('scalar')->end()
            ->end();
    }
}

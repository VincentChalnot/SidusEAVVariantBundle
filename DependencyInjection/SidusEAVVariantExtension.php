<?php

namespace Sidus\EAVVariantBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class SidusEAVVariantExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $dataClass = $container->getParameter('sidus_eav_model.entity.data.class');
        $valueClass = $container->getParameter('sidus_eav_model.entity.value.class');
        $defaultContext = $container->getParameter('sidus_eav_model.context.default_context');

        $container->setParameter('sidus_eav_variant.config.routes', $config['routes']);

        // Automatically declare a service for each family configured
        foreach ($config['families'] as $code => $familyConfiguration) {
            if (empty($familyConfiguration['data_class'])) {
                $familyConfiguration['data_class'] = $dataClass;
            }
            if (empty($familyConfiguration['value_class'])) {
                $familyConfiguration['value_class'] = $valueClass;
            }
            $familyConfiguration['default_context'] = $defaultContext;
            $this->addFamilyServiceDefinition($code, $familyConfiguration, $container);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('forms.yml');
        $loader->load('attribute_types.yml');
    }

    /**
     * @param string $code
     * @param array $familyConfiguration
     * @param ContainerBuilder $container
     * @throws BadMethodCallException
     * @throws InvalidArgumentException
     */
    protected function addFamilyServiceDefinition($code, $familyConfiguration, ContainerBuilder $container)
    {
        $definition = new Definition(new Parameter('sidus_eav_variant.family.class'), [
            $code,
            new Reference('sidus_eav_model.attribute_configuration.handler'),
            new Reference('sidus_eav_model.family_configuration.handler'),
            $familyConfiguration,
        ]);
        $definition->addMethodCall('setTranslator', [new Reference('translator')]);
        $definition->addTag('sidus.family');
        $sId = 'sidus_eav_variant.family.' . $code;
        $container->setDefinition($sId, $definition);
    }
}

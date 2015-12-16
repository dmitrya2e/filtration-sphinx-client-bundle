<?php

/*
 * This file is part of the Da2e FiltrationSphinxClientBundle package.
 *
 * (c) Dmitry Abrosimov <abrosimovs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Da2e\FiltrationSphinxClientBundle\DependencyInjection;

use Da2e\FiltrationSphinxClientBundle\Filter\SphinxClientHandlerType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This class handles applied configuration of the bundle and sets appropriate container parameters.
 *
 * @author Dmitry Abrosimov <abrosimovs@gmail.com>
 */
class Da2eFiltrationSphinxClientExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Loading additional configs.
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        // Register handler type.

        // 1. Get already registered handler types.
        if ($container->hasParameter('da2e.filtration.config.handler_types')) {
            $handlerTypes = $container->getParameter('da2e.filtration.config.handler_types');
        } else {
            $handlerTypes = [];
        }

        // 2. Add Sphinx client handler.
        $handlerTypes[SphinxClientHandlerType::TYPE] = $config['handler_class'];

        // 3. Update registered handler types as container parameter.
        $container->setParameter('da2e.filtration.config.handler_types', $handlerTypes);
    }
}

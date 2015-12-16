<?php

/*
 * This file is part of the Da2e FiltrationSphinxClientBundle package.
 *
 * (c) Dmitry Abrosimov <abrosimovs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Da2e\FiltrationSphinxClientBundle\Tests\DependencyInjection;

use Da2e\FiltrationSphinxClientBundle\DependencyInjection\Da2eFiltrationSphinxClientExtension;
use Da2e\FiltrationSphinxClientBundle\Filter\SphinxClientHandlerType;
use Da2e\FiltrationBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class Da2eFiltrationSphinxClientExtensionTest
 *
 * @author Dmitry Abrosimov <abrosimovs@gmail.com>
 */
class Da2eFiltrationSphinxClientExtensionTest extends TestCase
{
    public function testLoad()
    {
        $containerBuilder = new ContainerBuilder();
        $extension = new Da2eFiltrationSphinxClientExtension();

        $configs = [
            'da2e_filtration_sphinx_client' => [
                'handler_class' => '\stdClass',
            ],
        ];

        $extension->load($configs, $containerBuilder);

        $result = $containerBuilder->getParameter('da2e.filtration.config.handler_types');
        $this->assertTrue(is_array($result));
        $this->assertCount(1, $result);
        $this->assertArrayHasKey(SphinxClientHandlerType::TYPE, $result);
        $this->assertSame('\stdClass', $result[SphinxClientHandlerType::TYPE]);

        $enabledServices = [
            'da2e.filtration_sphinx_client.filter.text_filter',
            'da2e.filtration_sphinx_client.filter.number_filter',
            'da2e.filtration_sphinx_client.filter.date_filter',
            'da2e.filtration_sphinx_client.filter.choice_filter',
            'da2e.filtration_sphinx_client.filter.entity_filter',
        ];

        foreach ($enabledServices as $service) {
            $this->assertTrue($containerBuilder->has($service));
        }
    }

    public function testLoad_DefaultHandlerClass()
    {
        $containerBuilder = new ContainerBuilder();
        $extension = new Da2eFiltrationSphinxClientExtension();

        $extension->load([], $containerBuilder);

        $result = $containerBuilder->getParameter('da2e.filtration.config.handler_types');
        $this->assertTrue(is_array($result));
        $this->assertCount(1, $result);
        $this->assertArrayHasKey(SphinxClientHandlerType::TYPE, $result);
        $this->assertSame(SphinxClientHandlerType::CLASS_NAME, $result[SphinxClientHandlerType::TYPE]);
    }

    public function testLoad_ContainerHasOtherHandlerTypes()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('da2e.filtration.config.handler_types', ['foo' => 'bar']);

        $extension = new Da2eFiltrationSphinxClientExtension();
        $extension->load([], $containerBuilder);

        $result = $containerBuilder->getParameter('da2e.filtration.config.handler_types');
        $this->assertTrue(is_array($result));
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('foo', $result);
        $this->assertArrayHasKey(SphinxClientHandlerType::TYPE, $result);
        $this->assertSame('bar', $result['foo']);
        $this->assertSame(SphinxClientHandlerType::CLASS_NAME, $result[SphinxClientHandlerType::TYPE]);
    }
}

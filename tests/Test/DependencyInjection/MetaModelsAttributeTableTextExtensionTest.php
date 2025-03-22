<?php

/**
 * This file is part of MetaModels/attribute_tabletext.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_tabletext
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_tabletext/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTableTextBundle\Test\DependencyInjection;

use MetaModels\AttributeTableTextBundle\Attribute\AttributeTypeFactory;
use MetaModels\AttributeTableTextBundle\DependencyInjection\MetaModelsAttributeTableTextExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * This test case test the extension.
 *
 * @covers \MetaModels\AttributeTableTextBundle\DependencyInjection\MetaModelsAttributeTableTextExtension
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class MetaModelsAttributeTableTextExtensionTest extends TestCase
{
    public function testInstantiation(): void
    {
        $extension = new MetaModelsAttributeTableTextExtension();

        $this->assertInstanceOf(MetaModelsAttributeTableTextExtension::class, $extension);
        $this->assertInstanceOf(ExtensionInterface::class, $extension);
    }

    public function testFactoryIsRegistered(): void
    {
        $container = new ContainerBuilder();

        $extension = new MetaModelsAttributeTableTextExtension();
        $extension->load([], $container);

        self::assertTrue($container->hasDefinition('metamodels.attribute_tabletext.factory'));
        $definition = $container->getDefinition('metamodels.attribute_tabletext.factory');
        self::assertCount(1, $definition->getTag('metamodels.attribute_factory'));
    }
}

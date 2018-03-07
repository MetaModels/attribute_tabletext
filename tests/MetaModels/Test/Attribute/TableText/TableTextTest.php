<?php

/**
 * This file is part of MetaModels/attribute_tabletext.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage AttributeTableText
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Greminger <david.greminger@1up.io>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_tabletext/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test\Attribute\TableText;

use MetaModels\Attribute\TableText\TableText;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests to test class TableText.
 */
class TableTextTest extends TestCase
{
    /**
     * Test that the attribute can be instantiated.
     *
     * @return void
     */
    public function testInstantiation()
    {
        $text = new TableText($this->getMockForAbstractClass(IMetaModel::class));
        $this->assertInstanceOf(TableText::class, $text);
    }

    /**
     * Test saving with an empty row.
     *
     * @return void
     */
    public function testSavingEmptyRow()
    {
        $mockDB    = $this->getMockBuilder(\stdClass::class)->setMethods(['prepare'])->getMock();
        $container = $this->getMockForAbstractClass(IMetaModelsServiceContainer::class);
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);

        $metaModel->method('getServiceContainer')->willReturn($container);
        $container->method('getDatabase')->willReturn($mockDB);

        $mockQueries = $this
            ->getMockBuilder(\stdClass::class)
            ->setMethods(['execute', 'set'])
            ->getMock();

        $mockQueries->expects($this->exactly(6))->method('set')->withConsecutive(
            [['tstamp' => time(), 'value' => '1', 'att_id' => 42, 'row' => 0, 'col' => 0, 'item_id' => 21]],
            [['tstamp' => time(), 'value' => '2', 'att_id' => 42, 'row' => 0, 'col' => 1, 'item_id' => 21]],
            [['tstamp' => time(), 'value' => '3', 'att_id' => 42, 'row' => 0, 'col' => 2, 'item_id' => 21]],
            [['tstamp' => time(), 'value' => '4', 'att_id' => 42, 'row' => 2, 'col' => 0, 'item_id' => 21]],
            [['tstamp' => time(), 'value' => '5', 'att_id' => 42, 'row' => 2, 'col' => 1, 'item_id' => 21]],
            [['tstamp' => time(), 'value' => '6', 'att_id' => 42, 'row' => 2, 'col' => 2, 'item_id' => 21]]
        )->willReturn($mockQueries);

        $mockDB->expects($this->exactly(7))->method('prepare')->withConsecutive(
            ['DELETE FROM tl_metamodel_tabletext WHERE att_id=? AND item_id IN (21)'],
            ['INSERT INTO tl_metamodel_tabletext %s'],
            ['INSERT INTO tl_metamodel_tabletext %s'],
            ['INSERT INTO tl_metamodel_tabletext %s'],
            ['INSERT INTO tl_metamodel_tabletext %s'],
            ['INSERT INTO tl_metamodel_tabletext %s'],
            ['INSERT INTO tl_metamodel_tabletext %s']
        )->willReturn($mockQueries);

        $text = new TableText($metaModel, ['id' => 42]);

        $text->setDataFor(
            [
                21 => [
                    0 => [
                        0 => ['value' => '1', 'row' => 0, 'col' => 0],
                        1 => ['value' => '2', 'row' => 0, 'col' => 1],
                        2 => ['value' => '3', 'row' => 0, 'col' => 2],
                    ],
                    1 => [
                        0 => ['value' => '', 'row' => 1, 'col' => 0],
                        1 => ['value' => '', 'row' => 1, 'col' => 1],
                        2 => ['value' => '', 'row' => 1, 'col' => 2],
                    ],
                    2 => [
                        0 => ['value' => '4', 'row' => 2, 'col' => 0],
                        1 => ['value' => '5', 'row' => 2, 'col' => 1],
                        2 => ['value' => '6', 'row' => 2, 'col' => 2],
                    ],
                ]
            ]
        );
    }
}

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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_tabletext/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTableTextBundle\Test\Attribute;

use Doctrine\DBAL\Connection;
use MetaModels\AttributeTableTextBundle\Attribute\TableText;
use PHPUnit\Framework\TestCase;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;

/**
 * Unit tests to test class TableText.
 */
class TableTextTest extends TestCase
{
    /**
     * Mock a MetaModel.
     *
     * @param string $language         The language.
     * @param string $fallbackLanguage The fallback language.
     *
     * @return \MetaModels\IMetaModel
     */
    protected function mockMetaModel($language, $fallbackLanguage)
    {
        $metaModel = $this->getMock(
            'MetaModels\MetaModel',
            array(),
            array(array())
        );

        $metaModel
            ->expects($this->any())
            ->method('getTableName')
            ->will($this->returnValue('mm_unittest'));

        $metaModel
            ->expects($this->any())
            ->method('getActiveLanguage')
            ->will($this->returnValue($language));

        $metaModel
            ->expects($this->any())
            ->method('getFallbackLanguage')
            ->will($this->returnValue($fallbackLanguage));

        return $metaModel;
    }

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

    /**
     * Test retrieving of data with "holes".
     *
     * @return void
     */
    public function testRetrievingEmptyRow()
    {
        $mockDB    = $this->getMockBuilder(\stdClass::class)->setMethods(['prepare'])->getMock();
        $container = $this->getMockForAbstractClass(IMetaModelsServiceContainer::class);
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);

        $metaModel->method('getServiceContainer')->willReturn($container);
        $container->method('getDatabase')->willReturn($mockDB);

        $mockResult = $this->getMockBuilder(\stdClass::class)->setMethods(['next', 'row'])->getMock();

        $mockResult->expects($this->exactly(6))->method('next')
            ->willReturnOnConsecutiveCalls(true, true, true, true, true, false);
        $mockResult->method('row')->willReturnOnConsecutiveCalls(
            ['tstamp' => 123456789, 'value' => '1', 'att_id' => 42, 'row' => 0, 'col' => 0, 'item_id' => 21],
            ['tstamp' => 123456789, 'value' => '2', 'att_id' => 42, 'row' => 0, 'col' => 1, 'item_id' => 21],
            ['tstamp' => 123456789, 'value' => '3', 'att_id' => 42, 'row' => 0, 'col' => 2, 'item_id' => 21],
            ['tstamp' => 123456789, 'value' => '4', 'att_id' => 42, 'row' => 2, 'col' => 0, 'item_id' => 21],
            ['tstamp' => 123456789, 'value' => '6', 'att_id' => 42, 'row' => 2, 'col' => 2, 'item_id' => 21]
        );

        $mockQueries = $this
            ->getMockBuilder(\stdClass::class)
            ->setMethods(['execute', 'set'])
            ->getMock();
        $mockQueries->method('execute')->with([42])->willReturn($mockResult);


        $mockDB
            ->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM tl_metamodel_tabletext WHERE att_id=? AND item_id IN (21) ' .
                'ORDER BY item_id ASC, row ASC, col ASC')
            ->willReturn($mockQueries);

        $text = new TableText(
            $metaModel,
            [
                'id' => 42,
                'tabletext_cols' => serialize(
                    [
                        0 => ['rowLabel' => 'A', 'rowStyle' => '100px'],
                        1 => ['rowLabel' => 'B', 'rowStyle' => '100px'],
                        2 => ['rowLabel' => 'C', 'rowStyle' => '100px'],
                    ]
                )
            ]
        );

        $this->assertEquals(
            [21 => [
                0 => [
                    ['tstamp' => 123456789, 'value' => '1', 'att_id' => 42, 'row' => 0, 'col' => 0, 'item_id' => 21],
                    ['tstamp' => 123456789, 'value' => '2', 'att_id' => 42, 'row' => 0, 'col' => 1, 'item_id' => 21],
                    ['tstamp' => 123456789, 'value' => '3', 'att_id' => 42, 'row' => 0, 'col' => 2, 'item_id' => 21],
                ],
                1 => [
                    ['tstamp' => 0,         'value' => '', 'att_id' => 42, 'row' => 1, 'col' => 0, 'item_id' => 21],
                    ['tstamp' => 0,         'value' => '', 'att_id' => 42, 'row' => 1, 'col' => 1, 'item_id' => 21],
                    ['tstamp' => 0,         'value' => '', 'att_id' => 42, 'row' => 1, 'col' => 2, 'item_id' => 21],
                ],
                2 => [
                    ['tstamp' => 123456789, 'value' => '4', 'att_id' => 42, 'row' => 2, 'col' => 0, 'item_id' => 21],
                    ['tstamp' => 0,         'value' => '',  'att_id' => 42, 'row' => 2, 'col' => 1, 'item_id' => 21],
                    ['tstamp' => 123456789, 'value' => '6', 'att_id' => 42, 'row' => 2, 'col' => 2, 'item_id' => 21],
                ]
            ]],
            $text->getDataFor([21])
        );
    }

    /**
     * Test that the value to widget method works correctly.
     *
     * @return void
     */
    public function testValueToWidget()
    {
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);

        $text = new TableText(
            $metaModel,
            [
                'id' => 42,
                'tabletext_cols' => serialize(
                    [
                        0 => ['rowLabel' => 'A', 'rowStyle' => '100px'],
                        1 => ['rowLabel' => 'B', 'rowStyle' => '100px'],
                        2 => ['rowLabel' => 'C', 'rowStyle' => '100px'],
                    ]
                )
            ]
        );

        $this->assertEquals(
            [
                0 => ['col_0' => '1', 'col_1' => '2', 'col_2' => '3'],
                1 => ['col_0' => '',  'col_1' => '',  'col_2' => ''],
                2 => ['col_0' => '4', 'col_1' => '5', 'col_2' => '6'],
            ],
            $text->valueToWidget([
                0 => [
                    ['tstamp' => 123456789, 'value' => '1', 'att_id' => 42, 'row' => 0, 'col' => 0, 'item_id' => 21],
                    ['tstamp' => 123456789, 'value' => '2', 'att_id' => 42, 'row' => 0, 'col' => 1, 'item_id' => 21],
                    ['tstamp' => 123456789, 'value' => '3', 'att_id' => 42, 'row' => 0, 'col' => 2, 'item_id' => 21],
                ],
                1 => [
                    ['tstamp' => 123456789, 'value' => '', 'att_id' => 42, 'row' => 1, 'col' => 0, 'item_id' => 21],
                    ['tstamp' => 123456789, 'value' => '', 'att_id' => 42, 'row' => 1, 'col' => 1, 'item_id' => 21],
                    ['tstamp' => 123456789, 'value' => '', 'att_id' => 42, 'row' => 1, 'col' => 2, 'item_id' => 21],
                ],
                2 => [
                    ['tstamp' => 123456789, 'value' => '4', 'att_id' => 42, 'row' => 2, 'col' => 0, 'item_id' => 21],
                    ['tstamp' => 123456789, 'value' => '5', 'att_id' => 42, 'row' => 2, 'col' => 1, 'item_id' => 21],
                    ['tstamp' => 123456789, 'value' => '6', 'att_id' => 42, 'row' => 2, 'col' => 2, 'item_id' => 21]
                ]
            ])
        );
    }
}

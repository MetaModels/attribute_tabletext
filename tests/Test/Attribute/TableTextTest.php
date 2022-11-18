<?php

/**
 * This file is part of MetaModels/attribute_tabletext.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_tabletext
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Greminger <david.greminger@1up.io>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_tabletext/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTableTextBundle\Test\Attribute;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;
use MetaModels\AttributeTableTextBundle\Attribute\TableText;
use PHPUnit\Framework\TestCase;
use MetaModels\IMetaModel;

/**
 * Unit tests to test class TableText.
 *
 * @covers \MetaModels\AttributeTableTextBundle\Attribute\TableText
 */
class TableTextTest extends TestCase
{
    /**
     * Mock a MetaModel.
     *
     * @return \MetaModels\IMetaModel
     */
    protected function mockMetaModel()
    {
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);

        $metaModel
            ->method('getTableName')
            ->willReturn('mm_unittest');

        $metaModel
            ->method('getActiveLanguage')
            ->willReturn('en');

        $metaModel
            ->method('getFallbackLanguage')
            ->willReturn('de');

        return $metaModel;
    }

    /**
     * Mock the database connection.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Connection
     */
    private function mockConnection()
    {
        return $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test that the attribute can be instantiated.
     *
     * @return void
     */
    public function testInstantiation()
    {
        $text = new TableText(
            $this->getMockForAbstractClass(IMetaModel::class),
            [],
            $this->mockConnection()
        );
        $this->assertInstanceOf(TableText::class, $text);
    }

    /**
     * Test saving with an empty row.
     *
     * @return void
     */
    public function testSavingEmptyRow()
    {
        $mockDB    = $this->mockConnection();
        $metaModel = $this->mockMetaModel();

        $deleteBuilder = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();
        $deleteBuilder
            ->expects($this->once())
            ->method('delete')
            ->with('tl_metamodel_tabletext')
            ->willReturn($deleteBuilder);
        $deleteBuilder
            ->expects($this->once())
            ->method('execute');

        $mockDB
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($deleteBuilder);
        $mockDB
            ->expects($this->exactly(6))
            ->method('insert')
            ->withConsecutive(
                [
                    'tl_metamodel_tabletext',
                    [
                        'tl_metamodel_tabletext.tstamp'  => time(),
                        'tl_metamodel_tabletext.value'   => '1',
                        'tl_metamodel_tabletext.att_id'  => 42,
                        'tl_metamodel_tabletext.row'     => 0,
                        'tl_metamodel_tabletext.col'     => 0,
                        'tl_metamodel_tabletext.item_id' => 21
                    ]
                ],
                [
                    'tl_metamodel_tabletext',
                    [
                        'tl_metamodel_tabletext.tstamp'  => time(),
                        'tl_metamodel_tabletext.value'   => '2',
                        'tl_metamodel_tabletext.att_id'  => 42,
                        'tl_metamodel_tabletext.row'     => 0,
                        'tl_metamodel_tabletext.col'     => 1,
                        'tl_metamodel_tabletext.item_id' => 21
                    ]
                ],
                [
                    'tl_metamodel_tabletext',
                    [
                        'tl_metamodel_tabletext.tstamp'  => time(),
                        'tl_metamodel_tabletext.value'   => '3',
                        'tl_metamodel_tabletext.att_id'  => 42,
                        'tl_metamodel_tabletext.row'     => 0,
                        'tl_metamodel_tabletext.col'     => 2,
                        'tl_metamodel_tabletext.item_id' => 21
                    ]
                ],
                [
                    'tl_metamodel_tabletext',
                    [
                        'tl_metamodel_tabletext.tstamp'  => time(),
                        'tl_metamodel_tabletext.value'   => '4',
                        'tl_metamodel_tabletext.att_id'  => 42,
                        'tl_metamodel_tabletext.row'     => 2,
                        'tl_metamodel_tabletext.col'     => 0,
                        'tl_metamodel_tabletext.item_id' => 21
                    ]
                ],
                [
                    'tl_metamodel_tabletext',
                    [
                        'tl_metamodel_tabletext.tstamp'  => time(),
                        'tl_metamodel_tabletext.value'   => '5',
                        'tl_metamodel_tabletext.att_id'  => 42,
                        'tl_metamodel_tabletext.row'     => 2,
                        'tl_metamodel_tabletext.col'     => 1,
                        'tl_metamodel_tabletext.item_id' => 21
                    ]
                ],
                [
                    'tl_metamodel_tabletext',
                    [
                        'tl_metamodel_tabletext.tstamp'  => time(),
                        'tl_metamodel_tabletext.value'   => '6',
                        'tl_metamodel_tabletext.att_id'  => 42,
                        'tl_metamodel_tabletext.row'     => 2,
                        'tl_metamodel_tabletext.col'     => 2,
                        'tl_metamodel_tabletext.item_id' => 21
                    ]
                ]
            );

        $text = new TableText($metaModel, ['id' => 42], $mockDB);

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
        $mockDB    = $this->mockConnection();
        $metaModel = $this->mockMetaModel();

        $selectBuilder = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();
        $mockDB
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($selectBuilder);

        $selectBuilder
            ->expects($this->once())
            ->method('select')
            ->with('*')
            ->willReturn($selectBuilder);
        $selectBuilder
            ->expects($this->once())
            ->method('from')
            ->with('tl_metamodel_tabletext')
            ->willReturn($selectBuilder);
        $selectBuilder
            ->expects($this->once())
            ->method('orderBy')
            ->with('t.item_id', 'ASC')
            ->willReturn($selectBuilder);
        $selectBuilder
            ->expects($this->exactly(2))
            ->method('addOrderBy')
            ->withConsecutive(['t.row', 'ASC'], ['t.col', 'ASC'])
            ->willReturn($selectBuilder);

        $selectStatement = $this->getMockBuilder(Statement::class)->disableOriginalConstructor()->getMock();
        $selectStatement
            ->expects($this->exactly(6))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                ['tstamp' => 123456789, 'value' => '1', 'att_id' => 42, 'row' => 0, 'col' => 0, 'item_id' => 21],
                ['tstamp' => 123456789, 'value' => '2', 'att_id' => 42, 'row' => 0, 'col' => 1, 'item_id' => 21],
                ['tstamp' => 123456789, 'value' => '3', 'att_id' => 42, 'row' => 0, 'col' => 2, 'item_id' => 21],
                ['tstamp' => 123456789, 'value' => '4', 'att_id' => 42, 'row' => 2, 'col' => 0, 'item_id' => 21],
                ['tstamp' => 123456789, 'value' => '6', 'att_id' => 42, 'row' => 2, 'col' => 2, 'item_id' => 21],
                null
            );

        $selectBuilder
            ->expects($this->once())
            ->method('execute')
            ->willReturn($selectStatement);

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
            ],
            $mockDB
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
        $mockDB    = $this->mockConnection();
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
            ],
            $mockDB
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

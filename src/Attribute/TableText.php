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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     David Greminger <david.greminger@1up.io>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_tabletext/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTableTextBundle\Attribute;

use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\BaseComplex;
use MetaModels\IMetaModel;

use function array_column;
use function array_keys;
use function array_map;
use function array_merge;
use function array_search;
use function count;
use function implode;
use function is_array;
use function is_int;
use function str_replace;
use function time;

/**
 * This is the MetaModelAttribute class for handling table text fields.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class TableText extends BaseComplex
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * Instantiate an MetaModel attribute.
     *
     * Note that you should not use this directly but use the factory classes to instantiate attributes.
     *
     * @param IMetaModel      $objMetaModel The MetaModel instance this attribute belongs to.
     * @param array           $arrData      The information array, for attribute information, refer to documentation of
     *                                      table tl_metamodel_attribute and documentation of the certain attribute
     *                                      classes for information what values are understood.
     * @param Connection|null $connection   The database connection.
     */
    public function __construct(IMetaModel $objMetaModel, array $arrData = [], Connection $connection = null)
    {
        parent::__construct($objMetaModel, $arrData);

        if (null === $connection) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Connection is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $connection = System::getContainer()->get('database_connection');
            assert($connection instanceof Connection);
        }
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function searchFor($strPattern)
    {
        $strPattern = str_replace(['*', '?'], ['%', '_'], $strPattern);

        $statement = $this->connection
            ->createQueryBuilder()
            ->select('t.item_id')
            ->from($this->getValueTable(), 't')
            ->where('t.value LIKE :pattern')
            ->andWhere('t.att_id = :id')
            ->setParameter('pattern', $strPattern)
            ->setParameter('id', $this->get('id'))
            ->executeQuery();

        // Return value list as list<mixed>, parent function wants a list<string> so we make a cast.
        return array_map(static fn (mixed $value) => (string) $value, $statement->fetchFirstColumn());
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeSettingNames()
    {
        return array_merge(
            parent::getAttributeSettingNames(),
            ['tabletext_cols', 'tabletext_minCount', 'tabletext_maxCount', 'tabletext_disable_sorting']
        );
    }

    /**
     * Return the table we are operating on.
     *
     * @return string
     */
    protected function getValueTable()
    {
        return 'tl_metamodel_tabletext';
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldDefinition($arrOverrides = [])
    {
        $arrColLabels                    = StringUtil::deserialize($this->get('tabletext_cols'), true);
        $arrFieldDef                     = parent::getFieldDefinition($arrOverrides);
        $arrFieldDef['inputType']        = 'multiColumnWizard';
        $arrFieldDef['eval']['minCount'] = $this->get('tabletext_minCount') ?: '0';
        $arrFieldDef['eval']['maxCount'] = $this->get('tabletext_maxCount') ?: '0';

        if ($this->get('tabletext_disable_sorting')) {
            $arrFieldDef['eval']['buttons'] = [
                'move' => false,
                'up'   => false,
                'down' => false
            ];
        }

        if (!empty($arrFieldDef['eval']['readonly'])) {
            $arrFieldDef['eval']['hideButtons'] = true;
        }

        $arrFieldDef['eval']['columnFields'] = [];

        $countCol = count($arrColLabels);
        for ($i = 0; $i < $countCol; $i++) {
            // Init columnField.
            $arrFieldDef['eval']['columnFields']['col_' . $i] = [
                'label'     => $arrColLabels[$i]['rowLabel'],
                'inputType' => 'text',
                'eval'      => [],
            ];

            // Add readonly.
            if (!empty($arrFieldDef['eval']['readonly'])) {
                $arrFieldDef['eval']['columnFields']['col_' . $i]['eval']['readonly'] = true;
            }

            // Add style.
            if ($arrColLabels[$i]['rowStyle']) {
                $arrFieldDef['eval']['columnFields']['col_' . $i]['eval']['style'] =
                    'width:' . $arrColLabels[$i]['rowStyle'];
            }
        }

        return $arrFieldDef;
    }

    /**
     * {@inheritdoc}
     */
    public function setDataFor($arrValues)
    {
        // Check if we have an array.
        if (empty($arrValues)) {
            return;
        }

        // Get the ids.
        $arrIds = array_keys($arrValues);

        // Reset all data for the ids.
        $this->unsetDataFor($arrIds);

        foreach ($arrIds as $intId) {
            // Walk every row.
            foreach ((array) $arrValues[$intId] as $row) {
                // Walk every column and update / insert the value.
                foreach ($row as $col) {
                    // Skip empty cols but preserve cols containing '0'.
                    if ($this->getSetValues($col, $intId)[$this->getValueTable() . '.value'] === '') {
                        continue;
                    }

                    $this->connection->insert($this->getValueTable(), $this->getSetValues($col, $intId));
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * Fetch filter options from foreign table.
     */
    public function getFilterOptions($idList, $usedOnly, &$arrCount = null)
    {
        $builder = $this->connection->createQueryBuilder()
            ->select('t.value, COUNT(t.value) as mm_count')
            ->from($this->getValueTable(), 't')
            ->andWhere('t.att_id = :att_id')
            ->setParameter('att_id', $this->get('id'))
            ->groupBy('t.value');


        if (null !== $idList) {
            $builder
                ->andWhere('t.item_id IN (:id_list)')
                ->orderBy('FIELD(t.id,:id_list)')
                ->setParameter('id_list', $idList, ArrayParameterType::INTEGER);
        }

        $statement = $builder->executeQuery();

        $arrResult = [];
        while ($objRow = $statement->fetchAssociative()) {
            $strValue = $objRow['value'];

            if (is_array($arrCount)) {
                $arrCount[$strValue] = $objRow['mm_count'];
            }

            $arrResult[$strValue] = $strValue;
        }

        return $arrResult;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataFor($arrIds)
    {
        $arrWhere = $this->getWhere($arrIds, null, null, 't');
        $builder  = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->getValueTable(), 't')
            ->orderBy('t.item_id', 'ASC')
            ->addOrderBy('t.row', 'ASC')
            ->addOrderBy('t.col', 'ASC');

        if ($arrWhere) {
            $builder->andWhere($arrWhere['procedure']);

            foreach ($arrWhere['params'] as $name => $value) {
                $builder->setParameter($name, $value);
            }
        }

        $statement = $builder->executeQuery();

        $countCol = count(StringUtil::deserialize($this->get('tabletext_cols'), true));
        $result   = [];

        while ($content = $statement->fetchAssociative()) {
            $this->pushValue($content, $result, $countCol);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function unsetDataFor($arrIds)
    {
        $arrWhere = $this->getWhere($arrIds, null, null, $this->getValueTable());

        $builder = $this->connection->createQueryBuilder()
            ->delete($this->getValueTable());

        if ($arrWhere) {
            $builder->andWhere($arrWhere['procedure']);

            foreach ($arrWhere['params'] as $name => $value) {
                $builder->setParameter($name, $value);
            }
        }

        $builder->executeQuery();
    }

    /**
     * Build a where clause for the given id(s) and rows/cols.
     *
     * @param mixed    $mixIds     One, none or many ids to use.
     * @param int|null $intRow     The row number, optional.
     * @param int|null $intCol     The col number, optional.
     * @param string   $tableAlias The table alias.
     *
     * @return array<string, array<string, string|int|null>>
     */
    protected function getWhere($mixIds, $intRow = null, $intCol = null, $tableAlias = '')
    {
        if ('' !== $tableAlias) {
            $tableAlias .= '.';
        }

        $strWhereIds = '';
        $strRowCol   = '';
        if ($mixIds) {
            if (is_array($mixIds)) {
                $strWhereIds = ' AND ' . $tableAlias . 'item_id IN (' . implode(',', $mixIds) . ')';
            } else {
                $strWhereIds = ' AND ' . $tableAlias . 'item_id=' . $mixIds;
            }
        }

        if (is_int($intRow) && is_int($intCol)) {
            $strRowCol = ' AND ' . $tableAlias . 'row = :row AND ' . $tableAlias . 'col = :col';
        }

        return [
            'procedure' => $tableAlias . 'att_id=:att_id' . $strWhereIds . $strRowCol,
            'params'    => ($strRowCol)
                ? [
                    'att_id' => $this->get('id'),
                    'row'    => $intRow,
                    'col'    => $intCol
                ]
                : ['att_id' => $this->get('id')],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function valueToWidget($varValue)
    {
        if (!is_array($varValue)) {
            return [];
        }

        $arrColLabels = StringUtil::deserialize($this->get('tabletext_cols'), true);
        $countCol     = count($arrColLabels);
        $widgetValue  = [];

        foreach ($varValue as $k => $row) {
            for ($kk = 0; $kk < $countCol; $kk++) {
                $index = array_search($kk, array_column($row, 'col'), true);

                $widgetValue[$k]['col_' . $kk] = ($index !== false) ? $row[$index]['value'] : '';
            }
        }

        return $widgetValue;
    }

    /**
     * {@inheritdoc}
     */
    public function widgetToValue($varValue, $itemId)
    {
        /** @var array<int, array<string, string>>|null|string $varValue */
        if (!is_array($varValue)) {
            return [];
        }

        /** @var list<list<array{value: string, col: int, row: int}>> $newValue */
        $newValue = [];
        // Start row numerator at 0.
        $intRow = 0;
        foreach ($varValue as $row) {
            $newRow = [];
            foreach ($row as $colName => $value) {
                $newRow[] = [
                    'value' => $value,
                    'col' => (int) str_replace('col_', '', $colName),
                    'row' => $intRow,
                ];
            }
            $newValue[] = $newRow;
            $intRow++;
        }

        return $newValue;
    }

    /**
     * Calculate the array of query parameters for the given cell.
     *
     * @param array  $arrCell The cell to calculate.
     * @param string $intId   The data set id.
     *
     * @return array
     */
    protected function getSetValues($arrCell, $intId)
    {
        return [
            $this->getValueTable() . '.tstamp'  => time(),
            $this->getValueTable() . '.value'   => (string) $arrCell['value'],
            $this->getValueTable() . '.att_id'  => $this->get('id'),
            $this->getValueTable() . '.row'     => (int) $arrCell['row'],
            $this->getValueTable() . '.col'     => (int) $arrCell['col'],
            $this->getValueTable() . '.item_id' => $intId,
        ];
    }

    /**
     * Push a database value to the passed array.
     *
     * @param array $value    The value from the database.
     * @param array $result   The result list.
     * @param int   $countCol The count of columns per row.
     *
     * @return void
     */
    private function pushValue(array $value, array &$result, int $countCol): void
    {
        $buildRow = function (array &$list, int $itemId, int $row) use ($countCol): void {
            for ($i = count($list); $i < $countCol; $i++) {
                $list[$i] = [
                    'tstamp'  => 0,
                    'value'   => '',
                    'att_id'  => $this->get('id'),
                    'row'     => $row,
                    'col'     => $i,
                    'item_id' => $itemId,
                ];
            }
        };

        $itemId = $value['item_id'];
        if (!isset($result[$itemId])) {
            $result[$itemId] = [];
        }

        // Prepare all rows up until to this item.
        $row = count($result[$itemId]);
        while ($row <= $value['row']) {
            if (!isset($result[$itemId][$row])) {
                $result[$itemId][$row] = [];
            }
            $buildRow($result[$itemId][$row], $itemId, $row);
            $row++;
        }
        $result[$itemId][(int) $value['row']][(int) $value['col']] = $value;
    }
}

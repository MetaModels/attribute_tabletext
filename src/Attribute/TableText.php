<?php

/**
 * This file is part of MetaModels/attribute_tabletext.
 *
 * (c) 2012-2019 The MetaModels team.
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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_tabletext/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTableTextBundle\Attribute;

use Contao\StringUtil;
use Contao\System;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\BaseComplex;
use MetaModels\IMetaModel;

/**
 * This is the MetaModelAttribute class for handling table text fields.
 */
class TableText extends BaseComplex
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Instantiate an MetaModel attribute.
     *
     * Note that you should not use this directly but use the factory classes to instantiate attributes.
     *
     * @param IMetaModel      $objMetaModel The MetaModel instance this attribute belongs to.
     *
     * @param array           $arrData      The information array, for attribute information, refer to documentation of
     *                                      table tl_metamodel_attribute and documentation of the certain attribute
     *                                      classes for information what values are understood.
     *
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
        }

        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function searchFor($strPattern)
    {
        $query     = 'SELECT DISTINCT t.item_id FROM %1$s AS t WHERE value LIKE :value AND t.att_id = :id';
        $statement = $this->connection->prepare($query);
        $statement->bindValue('value', str_replace(array('*', '?'), array('%', '_'), $strPattern));
        $statement->bindValue('id', $this->get('id'));
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN, 'item_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeSettingNames()
    {
        return array_merge(parent::getAttributeSettingNames(), array(
            'tabletext_cols',
        ));
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
    public function getFieldDefinition($arrOverrides = array())
    {
        $arrColLabels                        = StringUtil::deserialize($this->get('tabletext_cols'), true);
        $arrFieldDef                         = parent::getFieldDefinition($arrOverrides);
        $arrFieldDef['inputType']            = 'multiColumnWizard';
        $arrFieldDef['eval']['columnFields'] = array();

        $countCol = count($arrColLabels);
        for ($i = 0; $i < $countCol; $i++) {
            $arrFieldDef['eval']['columnFields']['col_' . $i] = array(
                'label'     => $arrColLabels[$i]['rowLabel'],
                'inputType' => 'text',
                'eval'      => array(),
            );
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
                    if ($this->getSetValues($col, $intId)['value'] === '') {
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


        if ($idList) {
            $builder
                ->andWhere('t.item_id IN (:id_list)')

                ->orderBy('FIELD(t.id,:id_list)')
                ->setParameter('id_list', $idList, Connection::PARAM_INT_ARRAY);
        }

        $statement = $builder->execute();

        $arrResult = array();
        while ($objRow = $statement->fetch(\PDO::FETCH_OBJ)) {
            $strValue = $objRow->value;

            if (is_array($arrCount)) {
                $arrCount[$strValue] = $objRow->mm_count;
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
        $arrWhere = $this->getWhere($arrIds);
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

        $statement = $builder->execute();

        $countCol = count(StringUtil::deserialize($this->get('tabletext_cols'), true));
        $result   = [];

        while ($content = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $this->pushValue($content, $result, $countCol);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function unsetDataFor($arrIds)
    {
        $arrWhere = $this->getWhere($arrIds);

        $builder = $this->connection->createQueryBuilder()
            ->delete($this->getValueTable());

        if ($arrWhere) {
            $builder->andWhere($arrWhere['procedure']);

            foreach ($arrWhere['params'] as $name => $value) {
                $builder->setParameter($name, $value);
            }
        }

        $builder->execute();
    }

    /**
     * Build a where clause for the given id(s) and rows/cols.
     *
     * @param mixed    $mixIds One, none or many ids to use.
     *
     * @param int|null $intRow The row number, optional.
     *
     * @param int|null $intCol The col number, optional.
     *
     * @return array<string,string|array>
     */
    protected function getWhere($mixIds, $intRow = null, $intCol = null)
    {
        $strWhereIds = '';
        $strRowCol   = '';
        if ($mixIds) {
            if (is_array($mixIds)) {
                $strWhereIds = ' AND t.item_id IN (' . implode(',', $mixIds) . ')';
            } else {
                $strWhereIds = ' AND t.item_id=' . $mixIds;
            }
        }

        if (is_int($intRow) && is_int($intCol)) {
            $strRowCol = ' AND t.row = :row AND t.col = :col';
        }

        $arrReturn = array(
            'procedure' => 't.att_id=:att_id' . $strWhereIds . $strRowCol,
            'params' => ($strRowCol)
                ? array('t.att_id' => $this->get('id'), 't.row' => $intRow, 't.col' => $intCol)
                : array('t.att_id' => $this->get('id')),
        );

        return $arrReturn;
    }

    /**
     * {@inheritdoc}
     */
    public function valueToWidget($varValue)
    {
        if (!is_array($varValue)) {
            return array();
        }

        $arrColLabels = StringUtil::deserialize($this->get('tabletext_cols'), true);
        $countCol     = count($arrColLabels);
        $widgetValue  = array();

        foreach ($varValue as $k => $row) {
            for ($kk = 0; $kk < $countCol; $kk++) {
                $i = array_search($kk, array_column($row, 'col'));

                $widgetValue[$k]['col_' . $kk] = ($i !== false) ? $row[$i]['value'] : '';
            }
        }

        return $widgetValue;
    }

    /**
     * {@inheritdoc}
     */
    public function widgetToValue($varValue, $itemId)
    {
        if (!is_array($varValue)) {
            return array();
        }

        $newValue = array();
        // Start row numerator at 0.
        $intRow = 0;
        foreach ($varValue as $k => $row) {
            foreach ($row as $kk => $col) {
                $kk = str_replace('col_', '', $kk);

                $newValue[$k][$kk]['value'] = $col;
                $newValue[$k][$kk]['col']   = (int) $kk;
                $newValue[$k][$kk]['row']   = $intRow;
            }
            $intRow++;
        }

        return $newValue;
    }

    /**
     * Calculate the array of query parameters for the given cell.
     *
     * @param array $arrCell The cell to calculate.
     *
     * @param int   $intId   The data set id.
     *
     * @return array
     */
    protected function getSetValues($arrCell, $intId)
    {
        return array(
            'tstamp'  => time(),
            'value'   => (string) $arrCell['value'],
            'att_id'  => $this->get('id'),
            'row'     => (int) $arrCell['row'],
            'col'     => (int) $arrCell['col'],
            'item_id' => $intId,
        );
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
    private function pushValue($value, &$result, $countCol)
    {
        $buildRow = function (&$list, $itemId, $row) use ($countCol) {
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

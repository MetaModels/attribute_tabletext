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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_tabletext/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

/**
 * Table tl_metamodel_tabletext
 */
$GLOBALS['TL_DCA']['tl_metamodel_tabletext'] = array
(
    // Config
    'config' => array
    (
        'sql' => array
        (
            'keys' => array
            (
                'id'                        => 'primary',
                'att_id,item_id,row,col'    => 'index',
            )
        )
    ),
    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                     => 'int(10) unsigned NOT NULL auto_increment'
        ),
        'tstamp' => array
        (
            'sql'                     => 'int(10) unsigned NOT NULL default \'0\''
        ),
        'att_id' => array
        (
            'sql'                     => 'int(10) unsigned NOT NULL default \'0\''
        ),
        'item_id' => array
        (
            'sql'                     => 'int(10) unsigned NOT NULL default \'0\''
        ),
        'row' => array
        (
            'sql'                     => 'int(5) unsigned NOT NULL default \'0\''
        ),
        'col' => array
        (
            'sql'                     => 'int(5) unsigned NOT NULL default \'0\''
        ),
        'value' => array
        (
            'sql'                     => 'varchar(255) NOT NULL default \'\''
        )
    )
);

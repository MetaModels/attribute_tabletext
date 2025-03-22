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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_tabletext/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Table tl_metamodel_attribute
 */

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['metapalettes']['tabletext extends _complexattribute_'] = [
    '+advanced' => ['tabletext_cols', 'tabletext_minCount', 'tabletext_maxCount', 'tabletext_disable_sorting'],
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['tabletext_cols'] = [
    'label'       => 'tabletext_cols.label',
    'description' => 'tabletext_cols.description',
    'exclude'     => true,
    'inputType'   => 'multiColumnWizard',
    'eval'        => [
        'rgxp'          => 'digit',
        'mandatory'     => true,
        'useTranslator' => true,
        'columnFields'  => [
            'rowLabel' => [
                'label'       => 'tabletext_rowLabel.label',
                'description' => 'tabletext_rowLabel.description',
                'inputType'   => 'text',
                'eval'        => ['allowHtml' => false, 'style' => 'width: 100%;'],
            ],
            'rowStyle' => [
                'label'       => 'tabletext_rowStyle.label',
                'description' => 'tabletext_rowStyle.description',
                'inputType'   => 'text',
                'eval'        => ['allowHtml' => false, 'style' => 'width: 100%;'],
            ],
        ],
        'tl_class'      => 'clr w50',
    ],
    'sql'         => 'blob NULL'
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['tabletext_minCount'] = [
    'label'       => 'tabletext_minCount.label',
    'description' => 'tabletext_minCount.description',
    'exclude'     => true,
    'inputType'   => 'text',
    'eval'        => ['rgxp' => 'natural', 'maxlength' => 255, 'tl_class' => 'clr w50'],
    'sql'         => 'smallint(5) NOT NULL default \'0\''
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['tabletext_maxCount'] = [
    'label'       => 'tabletext_maxCount.label',
    'description' => 'tabletext_maxCount.description',
    'exclude'     => true,
    'inputType'   => 'text',
    'eval'        => ['rgxp' => 'natural', 'maxlength' => 255, 'tl_class' => 'w50'],
    'sql'         => 'smallint(5) NOT NULL default \'0\''
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['tabletext_disable_sorting'] = [
    'label'       => 'tabletext_disable_sorting.label',
    'description' => 'tabletext_disable_sorting.description',
    'exclude'     => true,
    'inputType'   => 'checkbox',
    'eval'        => ['tl_class' => 'clr w50 cbx m12'],
    'sql'         => 'char(1) NOT NULL default \'\''
];

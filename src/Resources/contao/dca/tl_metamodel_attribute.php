<?php

/**
 * This file is part of MetaModels/attribute_tabletext.
 *
 * (c) 2012-2020 The MetaModels team.
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
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_tabletext/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Table tl_metamodel_attribute
 */

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['metapalettes']['tabletext extends _complexattribute_'] = [
    '+advanced' => ['tabletext_cols'],
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['tabletext_cols'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['tabletext_cols'],
    'exclude'   => true,
    'inputType' => 'multiColumnWizard',
    'eval'      => [
        'rgxp'         => 'digit',
        'mandatory'    => true,
        'columnFields' => [
            'rowLabel' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['tabletext_rowLabel'],
                'inputType' => 'text',
                'eval'      => ['allowHtml' => false, 'style' => 'width: 500px;'],
            ],
            'rowStyle' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['tabletext_rowStyle'],
                'inputType' => 'text',
                'eval'      => ['allowHtml' => false, 'style' => 'width: 90px;'],
            ],
        ],
        'tl_class'     => 'clr w50',
    ],
    'sql'       => 'blob NULL'
];

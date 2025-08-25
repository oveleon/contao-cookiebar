<?php

declare(strict_types=1);

/*
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @author      Sebastian Zoglowek    <https://github.com/zoglo>
 * @copyright   Oveleon               <https://www.oveleon.de/>
 */

use Contao\DC_Table;
use Contao\DataContainer;

$GLOBALS['TL_DCA']['tl_cookie_group'] = [
    // Palettes
    'palettes' => [
        'default'                     => '{title_legend},title,published;description',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql'                     => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid' => [
            'foreignKey'              => 'tl_cookiebar.title',
            'sql'                     => "int(10) unsigned NOT NULL default 0",
            'relation'                => ['type' => 'belongsTo', 'load' => 'lazy'],
        ],
        'sorting' => [
            'sql'                     => "int(10) unsigned NOT NULL default 0",
        ],
        'identifier' => [
            'sql'                     => "varchar(255) NOT NULL default ''",
        ],
        'tstamp' => [
            'sql'                     => "int(10) unsigned NOT NULL default 0",
        ],
        'title' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'                     => "varchar(255) NOT NULL default ''",
        ],
        'description' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => ['rte' => 'tinyMCE', 'helpwizard' => true],
            'explanation'             => 'insertTags',
            'sql'                     => "mediumtext NULL",
        ],
        'published' => [
            'exclude'                 => true,
            'filter'                  => true,
            'toggle'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['doNotCopy' => true, 'tl_class' => 'w50 m12'],
            'sql'                     => "char(1) NOT NULL default ''",
        ]
    ],

    // Config
    'config' => [
        'dataContainer'               => DC_Table::class,
        'ptable'                      => 'tl_cookiebar',
        'ctable'                      => ['tl_cookie'],
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'markAsCopy'                  => 'title',
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid,published' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode'                    => DataContainer::MODE_PARENT,
            'fields'                  => ['sorting'],
            'headerFields'            => ['title'],
            'panelLayout'             => 'limit',
            'child_record_class'      => 'no_padding',
        ],
        'label' => [
            'fields'                  => ['title'],
            'format'                  => '%s',
        ],
        'global_operations' => [
            'all',
        ],
        'operations' => [
            'edit',
            'children',
            'copy',
            'cut',
            'delete',
            'toggle',
            'show',
        ],
    ],
];

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

use Contao\DataContainer;
use Contao\DC_Table;
use Oveleon\ContaoCookiebar\AbstractCookie;

$GLOBALS['TL_DCA']['tl_cookie_config'] = [
	// Palettes
	'palettes' => [
	    '__selector__'                => ['type'],
        'default'                     => '{title_legend},title,type;',
        'script'                      => '{title_legend},title,type;sourceUrl,sourceLoadingMode,sourceUrlParameter;scriptConfig,scriptPosition,scriptLoadingMode;',
        'googleConsentMode'           => '{title_legend},title,type;scriptConfig;',
        'tagManager'                  => '{title_legend},title,type;vendorId,googleConsentMode,scriptConfig;',
	],

    // Fields
	'fields' => [
        'id' => [
            'sql'                     => "int(10) unsigned NOT NULL auto_increment",
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
        'type' => [
            'exclude'                 => true,
            'filter'                  => true,
            'search'                  => true,
            'default'                 => 'script',
            'inputType'               => 'select',
            'options'                 => ['script','googleConsentMode','tagManager'],
            'reference'               => &$GLOBALS['TL_LANG']['tl_cookie_config'],
            'eval'                    => ['helpwizard' => true, 'submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'                     => ['name' => 'type', 'type' => 'string', 'length' => 64, 'default' => 'text'],
        ],
        'vendorId' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'                     => "varchar(255) NOT NULL default ''",
        ],
        'sourceUrl' => [
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => ['rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 255, 'dcaPicker'=>['do' => 'files', 'context' => 'file', 'icon' => 'pickfile.svg', 'fieldType' => 'radio', 'filesOnly' => true, 'extensions' => 'js'], 'addWizardClass' => false, 'tl_class' => 'w50'],
            'sql'                     => "varchar(255) NOT NULL default ''",
        ],
        'sourceLoadingMode' => [
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => [
                AbstractCookie::LOAD_CONFIRMED   => 'confirmed',
                AbstractCookie::LOAD_UNCONFIRMED => 'unconfirmed',
                AbstractCookie::LOAD_ALWAYS      => 'always'
            ],
            'reference'               => &$GLOBALS['TL_LANG']['tl_cookie_config'],
            'eval'                    => ['tl_class' => 'w50'],
            'sql'                     => "char(1) NOT NULL default ''",
        ],
        'sourceUrlParameter' => [
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'options'                 => ['async', 'defer'],
            'eval'                    => ['multiple' => true, 'tl_class' => 'w50 clr'],
            'sql'                     => "varchar(255) NOT NULL default ''",
        ],
		'scriptPosition' => [
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => [
                AbstractCookie::POS_BELOW => 'bodyBelowContent',
                AbstractCookie::POS_ABOVE => 'bodyAboveContent',
                AbstractCookie::POS_HEAD  => 'head',
            ],
            'reference'               => &$GLOBALS['TL_LANG']['tl_cookie_config'],
            'eval'                    => ['tl_class' => 'w50'],
            'sql'                     => "varchar(32) NOT NULL default ''",
        ],
        'scriptLoadingMode' => [
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => [
                AbstractCookie::LOAD_CONFIRMED   => 'confirmed',
                AbstractCookie::LOAD_UNCONFIRMED => 'unconfirmed',
                AbstractCookie::LOAD_ALWAYS      => 'always',
            ],
            'reference'               => &$GLOBALS['TL_LANG']['tl_cookie_config'],
            'eval'                    => ['tl_class' => 'w50'],
            'sql'                     => "char(1) NOT NULL default ''",
        ],
        'scriptConfig' => [
            'exclude'                 => true,
            'inputType'               => 'textarea',
            'eval'                    => ['preserveTags' => true, 'decodeEntities' => true, 'helpwizard' => true, 'class' => 'monospace', 'rte' => 'ace|javascript', 'tl_class' => 'clr'],
            'explanation'             => 'configurationScriptConfig',
            'sql'                     => "text NULL",
        ],
        'googleConsentMode' => [
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['tl_class' => 'w50 m12', 'submitOnChange' => true],
            'sql'                     => "char(1) NOT NULL default ''",
        ],
        'published' => [
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['tl_class' => 'w50 m12'],
            'sql'                     => "char(1) NOT NULL default '1'", // ToDo Boolean field migration
        ]
	],

    // Config
    'config' => [
        'dataContainer'               => DC_Table::class,
        'enableVersioning'            => true,
        'markAsCopy'                  => 'title',
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'published' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode'                    => DataContainer::MODE_SORTABLE,
            'flag'                    => DataContainer::SORT_INITIAL_LETTER_ASC,
            'fields'                  => ['title'],
            'panelLayout'             => 'sort,search,limit',
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
            'copy',
            'delete',
            'show',
        ],
    ],
];

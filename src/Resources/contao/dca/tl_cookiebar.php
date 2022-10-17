<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

use Contao\DC_Table;
use Contao\DataContainer;
use Contao\System;

System::loadLanguageFile('tl_cookiebar');

$GLOBALS['TL_DCA']['tl_cookiebar'] = [
	// Palettes
	'palettes' => array
	(
        'default'                     => '{title_legend},title;{meta_legend},description,infoDescription,alignment,blocking,buttonColorScheme,template,infoUrls,excludePages;{expert_legend:hide},cssID,essentialCookieLanguage,position,scriptPosition,version,updateVersion'
	),

    // Fields
	'fields' => [
        'id' => [
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp' => [
            'sql'                     => "int(10) unsigned NOT NULL default 0"
        ],
        'title' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
            'sql'                     => "varchar(255) NOT NULL default ''"
        ],
        'description' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => ['rte'=>'tinyMCE', 'helpwizard'=>true, 'tl_class' => 'w50'],
            'explanation'             => 'insertTags',
            'sql'                     => "mediumtext NULL"
        ],
        'infoDescription' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => ['rte'=>'tinyMCE', 'helpwizard'=>true, 'tl_class' => 'w50'],
            'explanation'             => 'insertTags',
            'sql'                     => "mediumtext NULL"
        ],
        'version' => [
            'inputType'               => 'text',
            'explanation'             => 'cookiebarVersion',
            'eval'                    => ['readonly'=>true, 'maxlength'=>255, 'tl_class'=>'w50 clr', 'helpwizard'=>true],
            'sql'                     => "int(10) unsigned NOT NULL default 1"
        ],
        'updateVersion' => [
            'inputType'               => 'checkbox',
            'eval'                    => ['tl_class'=>'w50 m12'],
            'sql'                     => "char(1) NOT NULL default ''"
        ],
        'infoUrls' => [
            'exclude'                 => true,
            'inputType'               => 'pageTree',
            'foreignKey'              => 'tl_page.title',
            'eval'                    => ['multiple'=>true, 'isSortable'=>true, 'fieldType'=>'checkbox', 'tl_class'=>'w50 clr'],
            'sql'                     => "blob NULL",
            'relation'                => ['type'=>'hasOne', 'load'=>'lazy']
        ],
        'excludePages' => [
            'exclude'                 => true,
            'inputType'               => 'pageTree',
            'foreignKey'              => 'tl_page.title',
            'eval'                    => ['multiple'=>true, 'fieldType'=>'checkbox', 'tl_class'=>'w50'],
            'relation'                => ['type'=>'hasOne', 'load'=>'lazy'],
            'sql'                     => "blob NULL"
        ],
        'template' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'select',
            'eval'                    => ['tl_class'=>'w50'],
            'sql'                     => "varchar(64) NOT NULL default 'cookiebar_default_deny'"
        ],
        'alignment' => [
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => [
                'cc-top'             => 'align-top',
                'cc-top cc-left'     => 'align-top-left',
                'cc-top cc-right'    => 'align-top-right',
                'cc-middle'          => 'align-middle',
                'cc-bottom'          => 'align-bottom',
                'cc-bottom cc-left'  => 'align-bottom-left',
                'cc-bottom cc-right' => 'align-bottom-right'
            ],
            'reference'               => $GLOBALS['TL_LANG']['tl_cookiebar'],
            'eval'                    => ['tl_class'=>'w50 clr'],
            'sql'                     => "varchar(32) NOT NULL default ''"
        ],
        'buttonColorScheme' => [
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => ['grayscale', 'highlight'],
            'reference'               => $GLOBALS['TL_LANG']['tl_cookiebar'],
            'eval'                    => ['includeBlankOption'=>true, 'blankOptionLabel'=>$GLOBALS['TL_LANG']['tl_cookiebar']['neutral'], 'tl_class'=>'w50 clr'],
            'sql'                     => "varchar(32) NOT NULL default 'highlight'"
        ],
        'blocking' => [
            'inputType'               => 'checkbox',
            'eval'                    => ['tl_class'=>'w50 m12'],
            'sql'                     => "char(1) NOT NULL default ''"
        ],
        'essentialCookieLanguage' => [
            'default'                 => $GLOBALS['TL_LANGUAGE'] ?? 'en',
            'exclude'                 => true,
            'inputType'               => 'select',
            'reference'               => $GLOBALS['TL_LANG']['tl_cookiebar'],
            'eval'                    => ['tl_class'=>'w50'],
            'sql'                     => "varchar(2) NOT NULL default ''"
        ],
        'scriptPosition' => [
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => ['head', 'body'],
            'reference'               => $GLOBALS['TL_LANG']['tl_cookiebar'],
            'eval'                    => ['tl_class'=>'w50'],
            'sql'                     => "varchar(32) NOT NULL default ''"
        ],
        'position' => [
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => ['bodyBelowContent', 'bodyAboveContent'],
            'reference'               => $GLOBALS['TL_LANG']['tl_cookiebar'],
            'eval'                    => ['tl_class'=>'w50 clr'],
            'sql'                     => "varchar(32) NOT NULL default ''"
        ],
        'cssID' => [
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => ['multiple'=>true, 'size'=>2, 'tl_class'=>'w50'],
            'sql'                     => "varchar(255) NOT NULL default ''"
        ]
	],

    // Config
    'config' => [
        'dataContainer'               => DC_Table::class,
        'ctable'                      => ['tl_cookie_group'],
        'switchToEdit'                => true,
        'sql' => [
            'keys' => [
                'id' => 'primary'
            ]
        ]
    ],

    // List
    'list' => [
        'sorting' => [
            'mode'                    => DataContainer::MODE_SORTED,
            'flag'                    => DataContainer::SORT_INITIAL_LETTER_ASC,
            'fields'                  => ['title'],
            'panelLayout'             => 'search,limit'
        ],
        'label' => [
            'fields'                  => ['title'],
            'format'                  => '%s'
        ],
        'global_operations' => [
            'cookieLog' => [
                'href'                => 'table=tl_cookie_log',
                'icon'                => 'diff.svg'
            ],
            'all' => [
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ],
        'operations' => [
            'edit' => [
                'href'                => 'table=tl_cookie_group',
                'icon'                => 'edit.svg'
            ],
            'editheader' => [
                'href'                => 'act=edit',
                'icon'                => 'header.svg'
            ],
            'copy' => [
                'href'                => 'act=copy',
                'icon'                => 'copy.svg'
            ],
            'delete' => [
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'href'                => 'act=show',
                'icon'                => 'show.svg'
            ]
        ]
    ],
];

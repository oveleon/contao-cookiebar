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

$GLOBALS['TL_DCA']['tl_cookie_log'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => DC_Table::class,
        'notCopyable'                 => true,
        'notEditable'                 => true,
        'notCreatable'                => true,
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
                'cid' => 'index'
			)
		)
	),

	// List
	'list' => array
	(
        'sorting' => array
        (
            'mode'                    => DataContainer::MODE_SORTABLE,
            'fields'                  => array('tstamp'),
            'flag'                    => 2,
            'panelLayout'             => 'filter;sort,search,limit'
        ),
        'label' => array
        (
            'fields'                  => array('id','cid','version','domain','url', 'ip','tstamp'),
            'showColumns'             => true,
        ),
		'global_operations' => array
		(
            'all' => array
            (
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ),
			'export' => array
			(
                'href'                => 'key=export',
				'icon'                => 'theme_export.svg'
			)
		),
		'operations' => array
		(
            'delete' => array
            (
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"'
            ),
            'show' => array
            (
                'href'                => 'act=show',
                'icon'                => 'show.svg'
            )
		)
	),

	// Palettes
	'palettes' => array
	(
        'default'                     => '{log_legend},cid,version,tstamp,domain,url,ip,config'
	),

    // Fields
	'fields' => array
	(
        'id' => array
        (
            'sorting'                 => true,
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'cid' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default 0"
        ),
        'version' => array
        (
            'sorting'                 => true,
            'sql'                     => "int(10) unsigned NOT NULL default 0"
        ),
        'tstamp' => array
        (
            'filter'                  => true,
            'sorting'                 => true,
            'flag'                    => 6,
            'sql'                     => "int(10) unsigned NOT NULL default 0"
        ),
        'domain' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie_log']['domain'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'url' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie_log']['url'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'ip' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie_log']['ip'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'config' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie_log']['config'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'sql'                     => "text NULL"
        ),
	)
);

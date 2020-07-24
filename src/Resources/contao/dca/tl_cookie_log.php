<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

$GLOBALS['TL_DCA']['tl_cookie_log'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
        'notCopyable'                 => true,
        'notEditable'                 => true,
        'notCreatable'                => true,
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
                'pid' => 'index'
			)
		)
	),

	// List
	'list' => array
	(
        'sorting' => array
        (
            'mode'                    => 1,
            'fields'                  => array('id','pid','version','domain','url', 'ip','tstamp'),
            'flag'                    => 1,
            'panelLayout'             => 'search,limit'
        ),
        'label' => array
        (
            'fields'                  => array('id','pid','version','domain','url', 'ip','tstamp'),
            'showColumns'             => true,
        ),
		'global_operations' => array
		(
			/*'export' => array
			(
                'href'                => 'key=exportConfigLog',
				'icon'                => 'theme_export.svg'
			)*/
		),
		'operations' => array
		(
            'delete' => array
            (
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
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
        'default'                     => '{log_legend},pid,version,tstamp,domain,url,ip,config'
	),

    // Fields
	'fields' => array
	(
        'id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'pid' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default 0"
        ),
        'version' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default 0"
        ),
        'tstamp' => array
        (
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

<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

$GLOBALS['TL_DCA']['tl_cookiebar'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
        'ctable'                      => array('tl_cookie_group'),
        'switchToEdit'                => true,
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary'
			)
		),
        'oncreate_callback'          => array(
            array('tl_cookiebar', 'createEssentialGroupAndCookies')
        )
	),

	// List
	'list' => array
	(
        'sorting' => array
        (
            'mode'                    => 1,
            'fields'                  => array('title'),
            'flag'                    => 1,
            'panelLayout'             => 'search,limit'
        ),
        'label' => array
        (
            'fields'                  => array('title'),
            'format'                  => '%s'
        ),
		'global_operations' => array
		(
            'cookieLog' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cookiebar']['cookieLog'],
                'href'                => 'table=tl_cookie_log',
                'icon'                => 'diff.svg'
            ),
			'all' => array
			(
			    'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			)
		),
		'operations' => array
		(
            'edit' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cookiebar']['edit'],
                'href'                => 'table=tl_cookie_group',
                'icon'                => 'edit.svg'
            ),
            'editheader' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cookiebar']['editheader'],
                'href'                => 'act=edit',
                'icon'                => 'header.svg'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cookiebar']['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.svg'
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cookiebar']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cookiebar']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.svg'
            )
		)
	),

	// Palettes
	'palettes' => array
	(
        'default'                     => '{title_legend},title;{meta_legend},description,alignment,blocking,template,infoUrls;{expert_legend:hide},cssID,position,version,updateVersion'
	),

    // Fields
	'fields' => array
	(
        'id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default 0"
        ),
        'title' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['title'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'description' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['description'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('rte'=>'tinyMCE', 'helpwizard'=>true),
            'explanation'             => 'insertTags',
            'sql'                     => "mediumtext NULL"
        ),
        'version' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['version'],
            'inputType'               => 'text',
            'explanation'             => 'cookiebarVersion',
            'eval'                    => array('readonly'=>true, 'maxlength'=>255, 'tl_class'=>'w50', 'helpwizard'=>true),
            'sql'                     => "int(10) unsigned NOT NULL default 1"
        ),
        'updateVersion' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['updateVersion'],
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'save_callback'           => array(
                array('tl_cookiebar', 'updateVersion')
            )
        ),
        'infoUrls' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['infoUrls'],
            'exclude'                 => true,
            'inputType'               => 'pageTree',
            'foreignKey'              => 'tl_page.title',
            'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'tl_class'=>'w50 clr'),
            'sql'                     => "blob NULL",
            'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
        ),
        'template' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['template'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'select',
            'options_callback' => static function ()
            {
                return Contao\Controller::getTemplateGroup('cookiebar_');
            },
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "varchar(64) NOT NULL default ''"
        ),
        'alignment' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['alignment'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => array('cc-top', 'cc-middle', 'cc-bottom'),
            'reference'               => $GLOBALS['TL_LANG']['tl_cookiebar'],
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "varchar(32) NOT NULL default ''"
        ),
        'blocking' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['blocking'],
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'position' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['position'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => array('bodyBelowContent', 'bodyAboveContent'),
            'reference'               => $GLOBALS['TL_LANG']['tl_cookiebar'],
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "varchar(32) NOT NULL default ''"
        ),
        'cssID' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['cssID'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('multiple'=>true, 'size'=>2, 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        )
	)
);

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_cookiebar extends Contao\Backend
{
    /**
     * Create essential group an cookies
     *
     * @param $table
     * @param $insertId
     * @param $rows
     * @param Contao\DataContainer $dc
     */
    public function createEssentialGroupAndCookies($table, $insertId, $rows, Contao\DataContainer $dc)
    {
        $essentialGroup = new Oveleon\ContaoCookiebar\CookieGroupModel();
        $essentialGroup->title = $GLOBALS['TL_LANG']['tl_cookiebar']['defaultEssentialGroupName'];
        $essentialGroup->pid = $insertId;
        $essentialGroup->identifier = 'lock';
        $essentialGroup->published = 1;
        $essentialGroup->save();

        $arrDefaultCookies = [
            [
                'Contao CSRF Token',
                'csrf_contao_csrf_token',
                $GLOBALS['TL_LANG']['tl_cookiebar']['noExpireTime'],
                $GLOBALS['TL_LANG']['tl_cookiebar']['defaultCsrfDescription'],
                'lock'
            ],
            [
                'Contao HTTPS CSRF Token',
                'csrf_https-contao_csrf_token',
                $GLOBALS['TL_LANG']['tl_cookiebar']['noExpireTime'],
                $GLOBALS['TL_LANG']['tl_cookiebar']['defaultHttpsCsrfDescription'],
                'lock'
            ],
            [
                'PHP SESSION ID',
                'PHPSESSID',
                $GLOBALS['TL_LANG']['tl_cookiebar']['noExpireTime'],
                $GLOBALS['TL_LANG']['tl_cookiebar']['defaultPhpSessionDescription'],
                'lock'
            ]
        ];

        foreach ($arrDefaultCookies as $arrCookie)
        {
            $newCookie = new Oveleon\ContaoCookiebar\CookieModel();
            $newCookie->pid = $essentialGroup->id;
            $newCookie->title = $arrCookie[0];
            $newCookie->type = 'default';
            $newCookie->token = $arrCookie[1];
            $newCookie->expireTime = $arrCookie[2];
            $newCookie->description = $arrCookie[3];
            $newCookie->identifier = $arrCookie[4];
            $newCookie->published = 1;
            $newCookie->save();
        }
    }

    /**
     * Update Version
     *
     * @param $varValue
     * @param $dc
     */
    public function updateVersion($varValue, $dc)
    {
        if($varValue)
        {
            $newVersion = ++$dc->activeRecord->version;

            // Update the database
            $this->Database->prepare("UPDATE tl_cookiebar SET version=$newVersion WHERE id=?")
                ->execute($dc->activeRecord->id);
        }
    }
}

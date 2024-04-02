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
use Contao\System;

System::loadLanguageFile('tl_cookiebar');

$GLOBALS['TL_DCA']['tl_cookiebar'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => DC_Table::class,
        'ctable'                      => array('tl_cookie_group'),
        'switchToEdit'                => true,
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary'
			)
		),
        'onload_callback'          => array(
            array('tl_cookiebar', 'checkEssentialGroup')
        ),
        'onsubmit_callback'          => array(
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
                'attributes'          => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"',
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
        'default'                     => '{title_legend},title;{meta_legend},description,infoDescription,alignment,buttonColorScheme,blocking,hideOnInit,template,infoUrls,excludePages;{expert_legend:hide},cssID,essentialCookieLanguage,position,scriptPosition,version,updateVersion;'
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
            'eval'                    => array('rte'=>'tinyMCE', 'helpwizard'=>true, 'tl_class' => 'w50'),
            'explanation'             => 'insertTags',
            'sql'                     => "mediumtext NULL"
        ),
        'infoDescription' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['infoDescription'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('rte'=>'tinyMCE', 'helpwizard'=>true, 'tl_class' => 'w50'),
            'explanation'             => 'insertTags',
            'sql'                     => "mediumtext NULL"
        ),
        'version' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['version'],
            'inputType'               => 'text',
            'explanation'             => 'cookiebarVersion',
            'eval'                    => array('readonly'=>true, 'maxlength'=>255, 'tl_class'=>'w50 clr', 'helpwizard'=>true),
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
            'eval'                    => array('multiple'=>true, 'isSortable'=>true, 'fieldType'=>'checkbox', 'tl_class'=>'w50 clr'),
            'sql'                     => "blob NULL",
            'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
        ),
        'excludePages' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['excludePages'],
            'exclude'                 => true,
            'inputType'               => 'pageTree',
            'foreignKey'              => 'tl_page.title',
            'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'tl_class'=>'w50'),
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
            'sql'                     => "varchar(64) NOT NULL default 'cookiebar_default_deny'"
        ),
        'alignment' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['alignment'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => array(
                'cc-top'             => 'align-top',
                'cc-top cc-left'     => 'align-top-left',
                'cc-top cc-right'    => 'align-top-right',
                'cc-middle'          => 'align-middle',
                'cc-bottom'          => 'align-bottom',
                'cc-bottom cc-left'  => 'align-bottom-left',
                'cc-bottom cc-right' => 'align-bottom-right'
            ),
            'reference'               => $GLOBALS['TL_LANG']['tl_cookiebar'],
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "varchar(32) NOT NULL default ''"
        ),
        'buttonColorScheme' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['buttonColorScheme'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => ['grayscale', 'highlight'],
            'reference'               => $GLOBALS['TL_LANG']['tl_cookiebar'],
            'eval'                    => array('includeBlankOption'=>true, 'blankOptionLabel'=>$GLOBALS['TL_LANG']['tl_cookiebar']['neutral'], 'tl_class'=>'w50'),
            'sql'                     => "varchar(32) NOT NULL default 'highlight'"
        ),
        'blocking' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['blocking'],
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'hideOnInit' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['hideOnInit'],
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'essentialCookieLanguage' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['essentialCookieLanguage'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'load_callback' => array
            (
                array('tl_cookiebar', 'addDefaultLanguage')
            ),
            'options_callback'        => array('tl_cookiebar', 'loadAvailableLanguages'),
            'reference'               => $GLOBALS['TL_LANG']['tl_cookiebar'],
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "varchar(2) NOT NULL default ''"
        ),
        'scriptPosition' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['scriptPosition'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => array('head', 'body'),
            'reference'               => $GLOBALS['TL_LANG']['tl_cookiebar'],
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "varchar(32) NOT NULL default ''"
        ),
        'position' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['position'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => array('bodyBelowContent', 'bodyAboveContent'),
            'reference'               => $GLOBALS['TL_LANG']['tl_cookiebar'],
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "varchar(32) NOT NULL default ''"
        ),
        'cssID' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookiebar']['cssID'],
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('multiple'=>true, 'size'=>2, 'tl_class'=>'w50'),
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
     * Return available languages
     *
     * @return array
     */
    public function loadAvailableLanguages(): array
    {
        $validLanguages = $this->getLanguages();
        $arrLanguages = ['en', 'de', 'fr', 'it', 'sv'];
        $arrReturn = [];

        foreach ($arrLanguages as $strLanguage) {
            $arrReturn[ $strLanguage ] = $validLanguages[ $strLanguage ] ?? $strLanguage;
        }

        return $arrReturn;
    }

    public function addDefaultLanguage($value): string
    {
        if ($value)
        {
            return $value;
        }

        $language = $GLOBALS['TL_LANGUAGE'] ?? 'en';

        if (!array_key_exists($language, $this->loadAvailableLanguages()))
        {
            return 'en';
        }

        return $language;
    }

    /**
     * Check if an essential group exists
     *
     * @param \Contao\DataContainer $dc
     * @return bool
     */
    public function hasEssentialGroup(Contao\DataContainer $dc): bool
    {
        $countEssentialGroup = Oveleon\ContaoCookiebar\CookieGroupModel::countBy(['pid=?', 'identifier=?'], [$dc->id, 'lock']);
        return null !== $countEssentialGroup && $countEssentialGroup >= 1;
    }

    /**
     * Check if essential groups are allowed to be created
     *
     * @param Contao\DataContainer $dc
     */
    public function checkEssentialGroup(Contao\DataContainer $dc)
    {
        if($this->hasEssentialGroup($dc))
        {
            $GLOBALS['TL_DCA']['tl_cookiebar']['fields']['essentialCookieLanguage']['eval']['disabled'] = true;
        }
    }

    /**
     * Create essential group an cookies
     *
     * @param Contao\DataContainer $dc
     */
    public function createEssentialGroupAndCookies(Contao\DataContainer $dc)
    {
        $strLang = $dc->activeRecord->essentialCookieLanguage;

        if(!$strLang || $this->hasEssentialGroup($dc))
        {
            return;
        }

        $translator = System::getContainer()->get('translator');
        $translator->setLocale($strLang);

        $essentialGroup = new Oveleon\ContaoCookiebar\CookieGroupModel();
        $essentialGroup->title = $translator->trans('tl_cookiebar.defaultEssentialGroupName', [], 'contao_tl_cookiebar');
        $essentialGroup->pid = $dc->id;
        $essentialGroup->identifier = 'lock';
        $essentialGroup->published = 1;
        $essentialGroup->tstamp = time();
        $essentialGroup->save();

        $arrDefaultCookies = [
            [
                'Contao CSRF Token',
                'csrf_contao_csrf_token',
                $translator->trans('tl_cookiebar.noExpireTime', [], 'contao_tl_cookiebar'),
                $translator->trans('tl_cookiebar.defaultCsrfDescription', [], 'contao_tl_cookiebar'),
                'lock'
            ],
            [
                'Contao HTTPS CSRF Token',
                'csrf_https-contao_csrf_token',
                $translator->trans('tl_cookiebar.noExpireTime', [], 'contao_tl_cookiebar'),
                $translator->trans('tl_cookiebar.defaultHttpsCsrfDescription', [], 'contao_tl_cookiebar'),
                'lock'
            ],
            [
                'PHP SESSION ID',
                'PHPSESSID',
                $translator->trans('tl_cookiebar.noExpireTime', [], 'contao_tl_cookiebar'),
                $translator->trans('tl_cookiebar.defaultPhpSessionDescription', [], 'contao_tl_cookiebar'),
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
            $newCookie->tstamp = time();
            $newCookie->save();
        }

        $translator->setLocale($GLOBALS['TL_LANGUAGE']);
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

            /** @var FOS\HttpCacheBundle\CacheManager $cacheManager */
            $cacheManager = Contao\System::getContainer()->get('fos_http_cache.cache_manager');
            $cacheManager->invalidateTags(array('oveleon.cookiebar.' . $dc->activeRecord->id));
        }
    }
}

<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

$GLOBALS['TL_DCA']['tl_cookie'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
        'ptable'                      => 'tl_cookie_group',
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'markAsCopy'                  => 'title',
        'onload_callback' => array
        (
            array('tl_cookie', 'checkPermission'),
            array('tl_cookie', 'adjustDcaByIdentifier'),
        ),
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
                'pid,published' => 'index'
			)
		)
	),

	// List
	'list' => array
	(
        'sorting' => array
        (
            'mode'                    => 4,
            'fields'                  => array('sorting'),
            'headerFields'            => array('title'),
            'panelLayout'             => 'limit',
            'child_record_callback'   => array('tl_cookie', 'listCookieItem'),
            'child_record_class'      => 'no_padding'
        ),
        'label' => array
        (
            'fields'                  => array('title'),
            'format'                  => '%s'
        ),
		'global_operations' => array
		(
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
                'label'               => &$GLOBALS['TL_LANG']['tl_cookie']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.svg'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cookie']['copy'],
                'href'                => 'act=paste&amp;mode=copy',
                'icon'                => 'copy.svg',
                'button_callback'     => array('tl_cookie', 'disableAction')
            ),
            'cut' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cookie']['cut'],
                'href'                => 'act=paste&amp;mode=cut',
                'icon'                => 'cut.svg',
                'attributes'          => 'onclick="Backend.getScrollOffset()"',
                'button_callback'     => array('tl_cookie', 'disableAction')
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cookie']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"',
                'button_callback'     => array('tl_cookie', 'disableAction')
            ),
            'toggle' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cookie']['toggle'],
                'icon'                => 'visible.svg',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'     => array('tl_cookie', 'toggleIcon')
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cookie']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.svg'
            )
		)
	),

	// Palettes
	'palettes' => array
	(
	    '__selector__'                => array('type'),
        'default'                     => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{description_legend:hide},description,detailDescription;published,checked,disabled;',
        'script'                      => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{global_config_legend:hide},globalConfig;sourceUrl,sourceLoadingMode,sourceUrlParameter;scriptConfirmed,scriptUnconfirmed,scriptPosition;{description_legend:hide},description,detailDescription;published,checked;',
        'template'                    => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{global_config_legend:hide},globalConfig;{template_legend},scriptTemplate,scriptPosition;{description_legend:hide},description,detailDescription;published,checked;',
        'googleAnalytics'             => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{google_analytics_legend},vendorId,scriptConfig;{description_legend:hide},description,detailDescription;published,disableForBeUser,checked;',
        'googleConsentMode'           => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{google_consent_mode_legend},globalConfig,gcmMode,scriptConfig;{description_legend:hide},description,detailDescription;published,checked;',
        'facebookPixel'               => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{facebook_pixel_legend},vendorId;{description_legend:hide},description,detailDescription;published,checked;',
        'matomo'                      => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{matomo_legend},vendorId,vendorUrl,scriptConfig,disableForBeUser;{description_legend:hide},description,detailDescription;published,checked;',
        'matomoTagManager'            => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{matomo_legend},vendorId,vendorUrl,scriptConfig;{description_legend:hide},description,detailDescription;published,checked;',
        'etracker'                    => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{matomo_legend},vendorId,blockCookies,scriptConfig;{description_legend:hide},description,detailDescription;published,checked;',
        'iframe'                      => '{title_legend},title,type,token,showTokens,expireTime,showExpireTime,provider,showProvider;{iframe_legend},iframeType,blockTemplate,blockDescription;{description_legend:hide},description,detailDescription;published,checked;',
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
            'foreignKey'              => 'tl_cookie_group.title',
            'sql'                     => "int(10) unsigned NOT NULL default 0",
            'relation'                => array('type'=>'belongsTo', 'load'=>'lazy')
        ),
        'sorting' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default 0"
        ),
        'identifier' => array
        (
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default 0"
        ),
        'title' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['title'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'token' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['token'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'clr w50'),
            'sql'                     => "varchar(255) NOT NULL default ''",
            'load_callback'           => array(
                array('tl_cookie', 'disableLockedField'),
                array('tl_cookie', 'requireField')
            ),
            'save_callback'           => array(
                array('tl_cookie', 'cleanupToken')
            ),
            'xlabel'                  => array
            (
                array('tl_cookie', 'selectTokenPreset')
            )
        ),
        'showTokens' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['showTokens'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('doNotCopy'=>true, 'tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'expireTime' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['expireTime'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'showExpireTime' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['showExpireTime'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('doNotCopy'=>true, 'tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default '1'"
        ),
        'provider' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['provider'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'showProvider' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['showProvider'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('doNotCopy'=>true, 'tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default '1'"
        ),
        'type' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['type'],
            'exclude'                 => true,
            'filter'                  => true,
            'default'                 => 'default',
            'inputType'               => 'select',
            'options'        => [
                'default',
                'script',
                'template',
                'googleAnalytics',
                'googleConsentMode',
                'facebookPixel',
                'matomo',
                'matomoTagManager',
                'etracker',
                'iframe'
            ],
            'reference'               => &$GLOBALS['TL_LANG']['tl_cookie'],
            'load_callback'           => array(
                array('tl_cookie', 'addTypeMessage'),
            ),
            'eval'                    => array('helpwizard'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                     => array('name'=>'type', 'type'=>'string', 'length'=>64, 'default'=>'text')
        ),
        'iframeType' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['iframeType'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'select',
            'options_callback'         => array('tl_cookie', 'getIframeTypes'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_cookie'],
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => array('name'=>'iframeType', 'type'=>'string', 'length'=>64, 'default'=>'')
        ),
        'blockTemplate' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['blockTemplate'],
            'default'                 => 'ccb_element_blocker',
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'select',
            'options_callback' => static function ()
            {
                return Contao\Controller::getTemplateGroup('ccb_element_');
            },
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "varchar(64) NOT NULL default ''"
        ),
        'scriptTemplate' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['scriptTemplate'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'select',
            'options_callback' => static function ()
            {
                return Contao\Controller::getTemplateGroup('analytics_');
            },
            'eval'                    => array('tl_class'=>'w50', 'mandatory'=>true),
            'sql'                     => "varchar(64) NOT NULL default ''"
        ),
        'vendorId' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['vendorId'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''",
            'load_callback'           => array(
                array('tl_cookie', 'overwriteTranslation')
            )
        ),
        'vendorUrl' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['vendorUrl'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''",
            'load_callback'           => array(
                array('tl_cookie', 'overwriteTranslation')
            )
        ),
        'description' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['description'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('rte'=>'tinyMCE', 'helpwizard'=>true, 'tl_class'=>'w50'),
            'explanation'             => 'insertTags',
            'sql'                     => "mediumtext NULL"
        ),
        'detailDescription' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['detailDescription'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('rte'=>'tinyMCE', 'helpwizard'=>true, 'tl_class'=>'w50'),
            'explanation'             => 'insertTags',
            'sql'                     => "mediumtext NULL"
        ),
        'blockDescription' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['blockDescription'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('rte'=>'tinyMCE', 'helpwizard'=>true, 'tl_class'=>'clr'),
            'explanation'             => 'insertTags',
            'sql'                     => "mediumtext NULL"
        ),
        'sourceUrl' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['sourceUrl'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'url', 'decodeEntities'=>true, 'maxlength'=>255, 'dcaPicker'=>array('do'=>'files', 'context'=>'file', 'icon'=>'pickfile.svg', 'fieldType'=>'radio', 'filesOnly'=>true, 'extensions'=>'js'), 'addWizardClass'=>false, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''",
            'save_callback'           => array(
                array('tl_cookie', 'addHostPrefix')
            )
        ),
        'sourceLoadingMode' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['sourceLoadingMode'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'select',
            'options'                 => array(
                1 => 'confirmed',
                2 => 'unconfirmed',
                3 => 'always'
            ),
            'reference'               => &$GLOBALS['TL_LANG']['tl_cookie'],
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'sourceUrlParameter' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['sourceUrlParameter'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'checkbox',
            'options'                 => array('async', 'defer'),
            'eval'                    => array('multiple'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'scriptConfirmed' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['scriptConfirmed'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('preserveTags'=>true, 'decodeEntities'=>true, 'class'=>'monospace', 'rte'=>'ace|javascript', 'tl_class'=>'clr'),
            'sql'                     => "text NULL"
        ),
        'scriptUnconfirmed' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['scriptUnconfirmed'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('preserveTags'=>true, 'decodeEntities'=>true, 'class'=>'monospace', 'rte'=>'ace|javascript', 'tl_class'=>'clr'),
            'sql'                     => "text NULL"
        ),
		'scriptPosition' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['scriptPosition'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => array(
                1 => 'bodyBelowContent',
                2 => 'bodyAboveContent',
                3 => 'head'
            ),
            'reference'               => &$GLOBALS['TL_LANG']['tl_cookie'],
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "varchar(32) NOT NULL default ''"
        ),
        'scriptConfig' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['scriptConfig'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('preserveTags'=>true, 'decodeEntities'=>true, 'helpwizard'=>true, 'class'=>'monospace', 'rte'=>'ace|javascript', 'tl_class'=>'clr'),
            'sql'                     => "text NULL",
            'explanation'             => 'cookiebarScriptConfig',
            'xlabel'                  => array
            (
                array('tl_cookie', 'selectScriptPreset')
            ),
        ),
        'globalConfig' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['globalConfig'],
            'exclude'                 => true,
			'inputType'               => 'picker',
			'foreignKey'              => 'tl_cookie_config.title',
			'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
			'sql'                     => "int(10) unsigned NOT NULL default 0",
			'relation'                => array('type'=>'hasOne', 'load'=>'lazy'),
            'load_callback'           => array
            (
                array('tl_cookie', 'loadGlobalConfigField')
            )
        ),
        'gcmMode' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['gcmDefaults'],
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => array('ad_storage','analytics_storage'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_cookie'],
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "varchar(32) NOT NULL default ''"
        ),
        'disabled' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['disabled'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default ''",
            'load_callback'           => array(
                array('tl_cookie', 'disableLockedField')
            )
        ),
        'checked' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['checked'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'blockCookies' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['blockCookies'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'published' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['published'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('doNotCopy'=>true, 'tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'disableForBeUser' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie']['disableForBeUser'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default ''"
        )
	)
);

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_cookie extends Contao\Backend
{
    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('Contao\BackendUser', 'User');
    }

    /**
     * Check permissions to edit table tl_cookie
     */
    public function checkPermission()
    {
        $strAct = Contao\Input::get('act');

        if($strAct == 'deleteAll' || $strAct == 'copyAll' || $strAct == 'cutAll')
        {
            /** @var Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
            $objSession = Contao\System::getContainer()->get('session');
            $session = $objSession->all();

            if($strAct == 'deleteAll')
            {
                $currentIds = $session['CURRENT']['IDS'];
            }
            else
            {
                $currentIds = $session['CLIPBOARD']['tl_cookie']['id'];
            }

            // Set allowed cookie IDs (delete multiple)
            if (is_array($currentIds))
            {
                $arrIds = array();

                foreach ($currentIds as $id)
                {
                    $objCookies = $this->Database->prepare("SELECT id, pid, identifier FROM tl_cookie WHERE id=?")
                        ->limit(1)
                        ->execute($id);

                    if ($objCookies->numRows < 1)
                    {
                        continue;
                    }

                    // Locked groups cannot be deleted or copied
                    if ($objCookies->identifier !== 'lock')
                    {
                        $arrIds[] = $id;
                    }
                }

                if($strAct == 'deleteAll')
                {
                    $session['CURRENT']['IDS'] = $arrIds;
                }
                else
                {
                    if(empty($arrIds))
                    {
                        $session['CLIPBOARD']['tl_cookie'] = $arrIds;
                    }
                    else
                    {
                        $session['CLIPBOARD']['tl_cookie']['id'] = $arrIds;
                    }
                }
            }

            // Overwrite session
            $objSession->replace($session);
        }
    }

    /**
     * Adjust dca by identifier
     *
     * @param $dc
     */
    public function adjustDcaByIdentifier($dc)
    {
        $objCookie = \Oveleon\ContaoCookiebar\CookieModel::findById($dc->id);

        if($objCookie)
        {
            $objGroup = \Oveleon\ContaoCookiebar\CookieGroupModel::findById($objCookie->pid);

            if($objCookie->identifier === 'lock' || $objGroup->identifier === 'lock')
            {
                $GLOBALS['TL_DCA']['tl_cookie']['palettes']['default'] = str_replace(',type', '', $GLOBALS['TL_DCA']['tl_cookie']['palettes']['default']);
            }
        }
    }

    /**
     * Add info messages
     *
     * @param $varValue
     * @param $dc
     *
     * @return mixed
     */
    public function addTypeMessage($varValue, $dc)
    {
        if($varValue === 'googleConsentMode')
        {
            Contao\Message::addInfo($GLOBALS['TL_LANG']['tl_cookie']['msgGoogleConsentMode']);
        }

        return $varValue;
    }

    /**
     * Add host prefix for source URLs from the same origin
     *
     * @param $varValue
     * @param $dc
     *
     * @return mixed
     */
    public function addHostPrefix($varValue, $dc)
    {
        if(!trim($varValue))
        {
            return $varValue;
        }

        if(
            (strpos($varValue, 'http') === 0) ||
            (strpos($varValue, 'https') === 0) ||
            (strpos($varValue, 'www') === 0) ||
            (strpos($varValue, '//') === 0) ||
            (strpos($varValue, '{{') === 0)
        )
        {
            return $varValue;
        }

        return '{{env::url}}/' . $varValue;
    }

    /**
     * Load translations for general fields
     *
     * @param $varValue
     * @param $dc
     *
     * @return mixed
     */
    public function overwriteTranslation($varValue, $dc)
    {
        $field = $dc->activeRecord->type . '_' . $dc->field;

        if($tl = $GLOBALS['TL_LANG']['tl_cookie'][$field])
        {
            $GLOBALS['TL_DCA']['tl_cookie']['fields'][$dc->field]['label'] = $tl;
        }

        return $varValue;
    }

    /**
     * Add button for adding default token configurations
     *
     * @param $dc
     *
     * @return mixed
     */
    public function selectTokenPreset($dc)
    {
        $id = 'token' . $dc->activeRecord->type;
        $strLangError = $GLOBALS['TL_LANG']['tl_cookie']['tokenConfig_'.$dc->activeRecord->type.'_error'] ?? '';

        return ' <a href="javascript:;" id="'.$id.'" title="' . $GLOBALS['TL_LANG']['tl_cookie']['tokenConfig_xlabel'] . '" onclick="Backend.getScrollOffset();var token=Cookiebar.getToken(\''.$dc->activeRecord->type.'\',\''.$strLangError.'\');if(token)document.getElementById(\'ctrl_'.$dc->field.'\').value=token">' . Contao\Image::getHtml('theme_import.svg', $GLOBALS['TL_LANG']['tl_cookie']['tokenConfig_xlabel']) . '</a><script>Cookiebar.issetToken(\''.$dc->activeRecord->type.'\',document.getElementById(\''.$id.'\'));</script>';
    }

    /**
     * Add button for adding default script configurations
     *
     * @param $dc
     *
     * @return mixed
     */
    public function selectScriptPreset($dc)
    {
        $id = 'script' . $dc->activeRecord->type;

        $xlabel  = ' <a href="javascript:;" id="script_'.$id.'" title="' . ($GLOBALS['TL_LANG']['tl_cookie']['scriptConfig_xlabel'] ?? '') . '" onclick="Backend.getScrollOffset();ace.edit(\'ctrl_' . $dc->field . '_div\').setValue(Cookiebar.getConfig(\''.$dc->activeRecord->type.'\'))">' . Contao\Image::getHtml('theme_import.svg', $GLOBALS['TL_LANG']['tl_cookie']['scriptConfig_xlabel']) . '</a><script>Cookiebar.issetConfig(\''.$dc->activeRecord->type.'\',document.getElementById(\'script_'.$id.'\'));</script>';
        $xlabel .= ' <a href="javascript:;" id="docs_'.$id.'" title="' . ($GLOBALS['TL_LANG']['tl_cookie']['scriptDocs_xlabel'] ?? '') . '" onclick="Backend.getScrollOffset();window.open(Cookiebar.getDocs(\''.$dc->activeRecord->type.'\'), \'_blank\')">' . Contao\Image::getHtml('show.svg', $GLOBALS['TL_LANG']['tl_cookie']['scriptConfig_xlabel']) . '</a><script>Cookiebar.issetDocs(\''.$dc->activeRecord->type.'\',document.getElementById(\'docs_'.$id.'\'));</script>';

        return $xlabel;
    }

    /**
     * List a group item
     *
     * @param array $arrRow
     *
     * @return string
     */
    public function listCookieItem($arrRow)
    {
        $strAddition = '';

        if($arrRow['vendorId'] && \in_array($arrRow['type'], ['googleAnalytics','facebookPixel','matomo','matomoTagManager']))
        {
            $strAddition = $arrRow['vendorId'];
        }
        elseif($arrRow['globalConfig'] && \in_array($arrRow['type'], ['script','template','googleConsentMode']))
        {
            $objConfig = Oveleon\ContaoCookiebar\CookieConfigModel::findById($arrRow['globalConfig']);

            if(null !== $objConfig)
            {
                $strAddition = $objConfig->title;
            }
        }

        return '<div class="tl_content_left">' . $arrRow['title'] . ' <span style="color:#999;padding-left:3px">[' . $GLOBALS['TL_LANG']['tl_cookie'][$arrRow['type']][0] . ($strAddition ?  ' | <span style="color:#f47c00;">' . $strAddition . '</span>' : '') . ']</span></div>';
    }

    /**
     * Return all iframe types
     *
     * @return array
     */
    public function getIframeTypes()
    {
        $arrTypes = \Contao\System::getContainer()->getParameter('contao_cookiebar.iframe_types');
        return array_keys($arrTypes);
    }

    /**
     * Return the delete cookie group button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function disableAction($row, $href, $label, $title, $icon, $attributes)
    {
        // Disable the button if the element is locked
        if ($row['identifier'] === 'lock')
        {
            return Contao\Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
        }

        return '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . Contao\StringUtil::specialchars($title) . '"' . $attributes . '>' . Contao\Image::getHtml($icon, $label) . '</a> ';
    }

    /**
     * Prepare global config field
     *
     * @param $varValue
     * @param $dc
     *
     * @return int
     *
     * @deprecated To be removed in 2.0. Provided for backward compatibility.
     */
    public function loadGlobalConfigField($varValue, $dc)
    {
        if($dc->activeRecord->type === 'googleConsentMode')
        {
            $GLOBALS['TL_DCA']['tl_cookie']['fields'][ $dc->field ]['eval']['mandatory'] = true;
        }

        return $varValue;
    }

    /**
     * Disable locked fields
     *
     * @param $varValue
     * @param $dc
     *
     * @return int
     */
    public function disableLockedField($varValue, $dc)
    {
        if($dc->activeRecord->identifier === 'lock')
        {
            $GLOBALS['TL_DCA']['tl_cookie']['fields'][ $dc->field ]['eval']['disabled'] = true;
        }

        return $varValue;
    }

    /**
     * Clean Up Token
     *
     * @param $varValue
     * @param $dc
     *
     * @return int
     */
    public function cleanupToken($varValue, $dc)
    {
        return str_replace(" ", "", $varValue);
    }

    /**
     * Require fields
     *
     * @param $varValue
     * @param $dc
     *
     * @return int
     */
    public function requireField($varValue, $dc)
    {
        $disableRequire = ['default', 'script', 'template', 'iframe', 'matomo', 'matomoTagManager', 'googleConsentMode'];

        if(in_array($dc->activeRecord->type, $disableRequire))
        {
            $GLOBALS['TL_DCA']['tl_cookie']['fields'][ $dc->field ]['eval']['mandatory'] = false;
        }

        return $varValue;
    }

    /**
     * Return the "toggle visibility" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $token = 'cid';

        if (strlen(Input::get($token)))
        {
            $this->toggleVisibility(Input::get($token), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->hasAccess('tl_cookie::published', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;id=' . Input::get('id') . '&amp;'.$token.'=' . $row['id'] . '&amp;state=' . $row['published'];

        if (!$row['published'])
        {
            $icon = 'invisible.svg';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . StringUtil::specialchars($title) . '" data-tid="cid"' . $attributes . '>' . Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';
    }

    /**
     * Toggle the visibility of an element
     *
     * @param integer              $intId
     * @param boolean              $blnVisible
     * @param Contao\DataContainer $dc
     *
     * @throws Contao\CoreBundle\Exception\AccessDeniedException
     */
    public function toggleVisibility($intId, $blnVisible, Contao\DataContainer $dc=null)
    {
        // Set the ID and action
        Contao\Input::setGet('id', $intId);
        Contao\Input::setGet('act', 'toggle');

        if ($dc)
        {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (is_array($GLOBALS['TL_DCA']['tl_cookie']['config']['onload_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_cookie']['config']['onload_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                }
                elseif (is_callable($callback))
                {
                    $callback($dc);
                }
            }
        }

        // Check the field access
        if (!$this->User->hasAccess('tl_cookie::published', 'alexf'))
        {
            throw new Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to show/hide content element ID ' . $intId . '.');
        }

        $objRow = $this->Database->prepare("SELECT * FROM tl_cookie WHERE id=?")
            ->limit(1)
            ->execute($intId);

        if ($objRow->numRows < 1)
        {
            throw new Contao\CoreBundle\Exception\AccessDeniedException('Invalid content element ID ' . $intId . '.');
        }

        // Set the current record
        if ($dc)
        {
            $dc->activeRecord = $objRow;
        }

        $objVersions = new Contao\Versions('tl_cookie', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array(($GLOBALS['TL_DCA']['tl_cookie']['fields']['published']['save_callback'] ?? null)))
        {
            foreach ($GLOBALS['TL_DCA']['tl_cookie']['fields']['published']['save_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
                }
                elseif (is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, $dc);
                }
            }
        }

        $time = time();

        // Update the database
        $this->Database->prepare("UPDATE tl_cookie SET tstamp=$time, published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
            ->execute($intId);

        if ($dc)
        {
            $dc->activeRecord->tstamp = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (is_array(($GLOBALS['TL_DCA']['tl_cookie']['config']['onsubmit_callback'] ?? null)))
        {
            foreach ($GLOBALS['TL_DCA']['tl_cookie']['config']['onsubmit_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                }
                elseif (is_callable($callback))
                {
                    $callback($dc);
                }
            }
        }

        $objVersions->create();
    }
}

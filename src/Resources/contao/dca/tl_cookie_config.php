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

$GLOBALS['TL_DCA']['tl_cookie_config'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => DC_Table::class,
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'markAsCopy'                  => 'title',
        'onload_callback' => array
        (
            array('tl_cookie_config', 'checkPermission')
        ),
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
                'published' => 'index'
			)
		)
	),

	// List
	'list' => array
	(
        'sorting' => array
        (
            'mode'                    => 2,
            'fields'                  => array('title'),
            'flag'                    => 1,
            'panelLayout'             => 'sort,search,limit'
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
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			)
		),
		'operations' => array
		(
            'edit' => array
            (
                'href'                => 'act=edit',
                'icon'                => 'edit.svg'
            ),
            'copy' => array
            (
                'href'                => 'act=paste&amp;mode=copy',
                'icon'                => 'copy.svg'
            ),
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
	    '__selector__'                => array('type'),
        'default'                     => '{title_legend},title,type;',
        'script'                      => '{title_legend},title,type;sourceUrl,sourceLoadingMode,sourceUrlParameter;scriptConfig,scriptPosition,scriptLoadingMode;',
        'tagManager'                  => '{title_legend},title,type;vendorId,googleConsentMode,scriptConfig;'
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
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'type' => array
        (
            'exclude'                 => true,
            'filter'                  => true,
            'search'                  => true,
            'default'                 => 'script',
            'inputType'               => 'select',
            'options'                 => array('script','tagManager'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_cookie_config'],
            'eval'                    => array('helpwizard'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50'),
            'sql'                     => array('name'=>'type', 'type'=>'string', 'length'=>64, 'default'=>'text')
        ),
        'vendorId' => array
        (
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''",
            'load_callback'           => array(
                array('tl_cookie_config', 'overwriteTranslation')
            )
        ),
        'sourceUrl' => array
        (
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'url', 'decodeEntities'=>true, 'maxlength'=>255, 'dcaPicker'=>array('do'=>'files', 'context'=>'file', 'icon'=>'pickfile.svg', 'fieldType'=>'radio', 'filesOnly'=>true, 'extensions'=>'js'), 'addWizardClass'=>false, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''",
            'save_callback'           => array(
                array('tl_cookie_config', 'addHostPrefix')
            )
        ),
        'sourceLoadingMode' => array
        (
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => array(
                1 => 'confirmed',
                2 => 'unconfirmed',
                3 => 'always'
            ),
            'reference'               => &$GLOBALS['TL_LANG']['tl_cookie_config'],
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'sourceUrlParameter' => array
        (
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'options'                 => array('async', 'defer'),
            'eval'                    => array('multiple'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
		'scriptPosition' => array
        (
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => array(
                1 => 'bodyBelowContent',
                2 => 'bodyAboveContent',
                3 => 'head'
            ),
            'reference'               => &$GLOBALS['TL_LANG']['tl_cookie_config'],
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "varchar(32) NOT NULL default ''"
        ),
        'scriptLoadingMode' => array
        (
            'exclude'                 => true,
            'inputType'               => 'select',
            'options'                 => array(
                1 => 'confirmed',
                2 => 'unconfirmed',
                3 => 'always'
            ),
            'reference'               => &$GLOBALS['TL_LANG']['tl_cookie_config'],
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'scriptConfig' => array
        (
            'exclude'                 => true,
            'inputType'               => 'textarea',
            'eval'                    => array('preserveTags'=>true, 'decodeEntities'=>true, 'helpwizard'=>true, 'class'=>'monospace', 'rte'=>'ace|javascript', 'tl_class'=>'clr'),
            'sql'                     => "text NULL",
            'explanation'             => 'configurationScriptConfig',
            'xlabel' => array
            (
                array('tl_cookie_config', 'selectScriptPreset')
            ),
        ),
        'googleConsentMode' => array
        (
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12', 'submitOnChange'=>true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'published' => array
        (
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default '1'"
        )
	)
);

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_cookie_config extends Contao\Backend
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
     * Check permissions to edit table tl_cookie_config
     */
    public function checkPermission()
    {
        if ($this->User->isAdmin)
        {
            return;
        }
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

        if($tl = $GLOBALS['TL_LANG']['tl_cookie_config'][$field])
        {
            $GLOBALS['TL_DCA']['tl_cookie_config']['fields'][$dc->field]['label'] = $tl;
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
     * Add button for adding default script configurations
     *
     * @param $dc
     *
     * @return mixed
     */
    public function selectScriptPreset($dc)
    {
        $this->loadLanguageFile('tl_cookie');

        $key = $dc->activeRecord->type;
        $id  = 'script' . $dc->activeRecord->type;

        if($dc->activeRecord->googleConsentMode)
        {
            $key .= '_gcm';
        }

        $xlabel  = ' <a href="javascript:;" id="script_'.$id.'" title="' . ($GLOBALS['TL_LANG']['tl_cookie']['scriptConfig_xlabel'] ?? '') . '" onclick="Backend.getScrollOffset();ace.edit(\'ctrl_' . $dc->field . '_div\').setValue(Cookiebar.getConfig(\''.$key.'\'))">' . Contao\Image::getHtml('theme_import.svg', $GLOBALS['TL_LANG']['tl_cookie']['scriptConfig_xlabel']) . '</a><script>Cookiebar.issetConfig(\''.$key.'\',document.getElementById(\'script_'.$id.'\'));</script>';
        $xlabel .= ' <a href="javascript:;" id="docs_'.$id.'" title="' . ($GLOBALS['TL_LANG']['tl_cookie']['scriptDocs_xlabel'] ?? '') . '" onclick="Backend.getScrollOffset();window.open(Cookiebar.getDocs(\''.$key.'\'), \'_blank\')">' . Contao\Image::getHtml('show.svg', $GLOBALS['TL_LANG']['tl_cookie']['scriptConfig_xlabel']) . '</a><script>Cookiebar.issetDocs(\''.$key.'\',document.getElementById(\'docs_'.$id.'\'));</script>';

        return $xlabel;
    }
}

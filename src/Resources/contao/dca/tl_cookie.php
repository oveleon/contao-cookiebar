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
            'cut' => array
            (
                'href'                => 'act=paste&amp;mode=cut',
                'icon'                => 'cut.svg',
                'attributes'          => 'onclick="Backend.getScrollOffset()"'
            ),
            'delete' => array
            (
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
                'button_callback'     => array('tl_cookie', 'deleteCookie')
            ),
            'toggle' => array
            (
                'icon'                => 'visible.svg',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'     => array('tl_cookie', 'toggleIcon')
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
        'default'                     => '{title_legend},title,token,expireTime,provider,type;{description_legend:hide},description;published;',
        'script'                      => '{title_legend},title,token,expireTime,provider,type;sourceUrl,sourceLoadingMode,sourceUrlParameter;scriptConfirmed,scriptUnconfirmed,scriptPosition;{description_legend:hide},description;published;',
        'googleAnalytics'             => '{title_legend},title,token,expireTime,provider,type;{google_analytics_legend},vendorId,scriptConfig;{description_legend:hide},description;published;',
        'facebookPixel'               => '{title_legend},title,token,expireTime,provider,type;{facebook_pixel_legend},vendorId;{description_legend:hide},description;published;',
        'matomo'                      => '{title_legend},title,token,expireTime,provider,type;{matomo_legend},vendorId,vendorUrl;{description_legend:hide},description;published;',
        'youtube'                     => '{title_legend},title,token,expireTime,provider,type;{youtube_legend},blockDescription;{description_legend:hide},description;published;',
        'vimeo'                       => '{title_legend},title,token,expireTime,provider,type;{vimeo_legend},blockDescription;{description_legend:hide},description;published;'
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
            'sql'                     => "varchar(255) NOT NULL default ''",
            'eval'                    => array('doNotCopy'=>true)
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
        'token' => array
        (
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'expireTime' => array
        (
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'provider' => array
        (
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'type' => array
        (
            'exclude'                 => true,
            'filter'                  => true,
            'default'                 => 'default',
            'inputType'               => 'select',
            'options'                 => array('default','script','googleAnalytics','facebookPixel','matomo','youtube','vimeo'),
            'reference'               => &$GLOBALS['TL_LANG']['tl_cookie'],
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
                array('tl_cookie', 'overwriteTranslation')
            )
        ),
        'vendorUrl' => array
        (
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'rgxp'=>'natural', 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''",
            'load_callback'           => array(
                array('tl_cookie', 'overwriteTranslation')
            )
        ),
        'description' => array
        (
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('rte'=>'tinyMCE', 'helpwizard'=>true),
            'explanation'             => 'insertTags',
            'sql'                     => "mediumtext NULL"
        ),
        'blockDescription' => array
        (
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('rte'=>'tinyMCE', 'helpwizard'=>true),
            'explanation'             => 'insertTags',
            'sql'                     => "mediumtext NULL"
        ),
        'sourceUrl' => array
        (
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'url', 'decodeEntities'=>true, 'maxlength'=>255, 'dcaPicker'=>true, 'addWizardClass'=>false, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'sourceLoadingMode' => array(
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'select',
            'options'                 => array(
                1 => 'confirmed',
                2 => 'unconfirmed',
                3 => 'always'
            ),
            'eval'                    => array('tl_class'=>'w50'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'sourceUrlParameter' => array
        (
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'checkbox',
            'options'                 => array('async', 'defer'),
            'eval'                    => array('multiple'=>true, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'scriptConfirmed' => array
        (
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('preserveTags'=>true, 'decodeEntities'=>true, 'class'=>'monospace', 'rte'=>'ace|javascript', 'tl_class'=>'clr'),
            'sql'                     => "text NULL"
        ),
        'scriptUnconfirmed' => array
        (
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('preserveTags'=>true, 'decodeEntities'=>true, 'class'=>'monospace', 'rte'=>'ace|javascript', 'tl_class'=>'clr'),
            'sql'                     => "text NULL"
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
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "varchar(32) NOT NULL default ''"
        ),
        'scriptConfig' => array
        (
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('preserveTags'=>true, 'decodeEntities'=>true, 'class'=>'monospace', 'rte'=>'ace|javascript', 'tl_class'=>'clr'),
            'sql'                     => "text NULL",
            'xlabel' => array
            (
                array('tl_cookie', 'selectScriptPreset')
            ),
        ),
        'published' => array
        (
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('doNotCopy'=>true, 'tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default ''",
            'load_callback' => array
            (
                array('tl_cookie', 'disableLockedField')
            )
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
        if(Contao\Input::get('act') == 'deleteAll')
        {
            /** @var Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
            $objSession = Contao\System::getContainer()->get('session');

            $session = $objSession->all();

            // Set allowed cookie IDs (delete multiple)
            if (is_array($session['CURRENT']['IDS']))
            {
                $delete_all = array();

                foreach ($session['CURRENT']['IDS'] as $id)
                {
                    $objCookies = $this->Database->prepare("SELECT id, pid, identifier FROM tl_cookie WHERE id=?")
                        ->limit(1)
                        ->execute($id);

                    if ($objCookies->numRows < 1)
                    {
                        continue;
                    }

                    // Locked groups cannot be deleted
                    if ($objCookies->identifier !== 'lock')
                    {
                        $delete_all[] = $id;
                    }
                }

                $session['CURRENT']['IDS'] = $delete_all;
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
        $objGroup = \Oveleon\ContaoCookiebar\CookieGroupModel::findById($objCookie->pid);

        if($objCookie->identifier === 'lock' || $objGroup->identifier === 'lock')
        {
            $GLOBALS['TL_DCA']['tl_cookie']['palettes']['default'] = str_replace(',type', '', $GLOBALS['TL_DCA']['tl_cookie']['palettes']['default']);
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

        if($tl = $GLOBALS['TL_LANG']['tl_cookie'][$field])
        {
            $GLOBALS['TL_DCA']['tl_cookie']['fields'][$dc->field]['label'] = $tl;
        }

        return $varValue;
    }

    /**
     * Add button for adding default configurations
     *
     * @param $dc
     *
     * @return mixed
     */
    public function selectScriptPreset($dc)
    {
        $id = 'script' . $dc->activeRecord->type;
        return ' <a href="javascript:;" id="'.$id.'" title="' . $GLOBALS['TL_LANG']['tl_cookie']['scriptConfig_xlabel'] . '" onclick="Backend.getScrollOffset();ace.edit(\'ctrl_' . $dc->field . '_div\').setValue(Cookiebar.get(\''.$dc->activeRecord->type.'\'))">' . Contao\Image::getHtml('theme_import.svg', $GLOBALS['TL_LANG']['tl_cookie']['scriptConfig_xlabel']) . '</a><script>Cookiebar.isset(\''.$dc->activeRecord->type.'\',document.getElementById(\''.$id.'\'));</script>';
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
            return 1;
        }

        return $varValue;
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
        return '<div class="tl_content_left">' . $arrRow['title'] . ' <span style="color:#999;padding-left:3px">[' . $GLOBALS['TL_LANG']['tl_cookie'][$arrRow['type']][0] . ($arrRow['vendorId'] ?  ' | <span style="color:#f47c00;">' . $arrRow['vendorId'] . '</span>' : '') . ']</span></div>';
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
    public function deleteCookie($row, $href, $label, $title, $icon, $attributes)
    {
        // Disable the button if the element is locked
        if ($row['identifier'] === 'lock')
        {
            return Contao\Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
        }

        return '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . Contao\StringUtil::specialchars($title) . '"' . $attributes . '>' . Contao\Image::getHtml($icon, $label) . '</a> ';
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
        if (Contao\Input::get('cid'))
        {
            $this->toggleVisibility(Contao\Input::get('cid'), (Contao\Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the cid, so hacking attempts are logged
        if (!$this->User->hasAccess('tl_cookie::published', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;id=' . Contao\Input::get('id') . '&amp;cid=' . $row['id'] . '&amp;state=' . $row['published'];

        if (!$row['published'])
        {
            $icon = 'invisible.svg';
        }

        if ($row['identifier'] === 'lock')
        {
            $icon  = 'unpublished.svg';
            $title = $GLOBALS['TL_LANG']['tl_cookie']['msgUnpublished'];
        }

        return '<a href="' . ($row['identifier'] === 'lock' ? 'javascript:;' : $this->addToUrl($href)) . '" title="' . Contao\StringUtil::specialchars($title) . '" data-tid="cid"' . ($row['identifier'] !== 'lock' ? $attributes : 'onclick="return false;"') . '>' . Contao\Image::getHtml($icon, $label, 'data-state="' . (!$row['published'] ? 0 : 1) . '"') . '</a> ';
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
        if (is_array($GLOBALS['TL_DCA']['tl_cookie']['fields']['published']['save_callback']))
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
        if (is_array($GLOBALS['TL_DCA']['tl_cookie']['config']['onsubmit_callback']))
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

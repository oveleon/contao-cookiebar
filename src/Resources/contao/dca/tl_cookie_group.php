<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

$GLOBALS['TL_DCA']['tl_cookie_group'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'ptable'                      => 'tl_cookiebar',
        'ctable'                      => array('tl_cookie'),
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'markAsCopy'                  => 'title',
        'onload_callback' => array
        (
            array('tl_cookie_group', 'checkPermission')
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
            'child_record_callback'   => array('tl_cookie_group', 'listCookieGroup'),
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
                'label'               => &$GLOBALS['TL_LANG']['tl_cookie_group']['edit'],
                'href'                => 'table=tl_cookie',
                'icon'                => 'edit.svg'
            ),
            'editheader' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cookie_group']['editheader'],
                'href'                => 'act=edit',
                'icon'                => 'header.svg'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cookie_group']['copy'],
                'href'                => 'act=paste&amp;mode=copy',
                'icon'                => 'copy.svg',
                'button_callback'     => array('tl_cookie_group', 'disableAction')
            ),
            'cut' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cookie_group']['cut'],
                'href'                => 'act=paste&amp;mode=cut',
                'icon'                => 'cut.svg',
                'attributes'          => 'onclick="Backend.getScrollOffset()"',
                'button_callback'     => array('tl_cookie_group', 'disableAction')
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cookie_group']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null . '\'))return false;Backend.getScrollOffset()"',
                'button_callback'     => array('tl_cookie_group', 'disableAction')
            ),
            'toggle' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cookie_group']['toggle'],
                'icon'                => 'visible.svg',
                'attributes'          => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback'     => array('tl_cookie_group', 'toggleGroupIcon'),
                'showInHeader'        => true
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_cookie_group']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.svg'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'                     => '{title_legend},title,published;description'
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
            'foreignKey'              => 'tl_cookiebar.title',
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
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie_group']['title'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'description' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie_group']['description'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => array('rte'=>'tinyMCE', 'helpwizard'=>true),
            'explanation'             => 'insertTags',
            'sql'                     => "mediumtext NULL"
        ),
        'published' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_cookie_group']['published'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('doNotCopy'=>true, 'tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default ''"
        )
    )
);

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_cookie_group extends Contao\Backend
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
     * Check permissions to edit table tl_cookie_group
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
                $currentIds = $session['CLIPBOARD']['tl_cookie_group']['id'];
            }

            // Set allowed cookie group IDs (delete multiple)
            if (is_array($currentIds))
            {
                $arrIds = array();

                foreach ($currentIds as $id)
                {
                    $objGroup = $this->Database->prepare("SELECT id, pid, identifier FROM tl_cookie_group WHERE id=?")
                        ->limit(1)
                        ->execute($id);

                    if ($objGroup->numRows < 1)
                    {
                        continue;
                    }

                    // Locked groups cannot be deleted
                    if ($objGroup->identifier !== 'lock')
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
                        $session['CLIPBOARD']['tl_cookie_group'] = $arrIds;
                    }
                    else
                    {
                        $session['CLIPBOARD']['tl_cookie_group']['id'] = $arrIds;
                    }
                }
            }

            // Overwrite session
            $objSession->replace($session);
        }
    }

    /**
     * List a group item
     *
     * @param array $arrRow
     *
     * @return string
     */
    public function listCookieGroup($arrRow)
    {
        return '<div class="tl_content_left">' . $arrRow['title'] . '</div>';
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
    public function toggleGroupIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (Contao\Input::get('tid'))
        {
            $this->toggleGroupVisibility(Contao\Input::get('tid'), (Contao\Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->hasAccess('tl_cookie_group::published', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published'])
        {
            $icon = 'invisible.svg';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . Contao\StringUtil::specialchars($title) . '"' . $attributes . '>' . Contao\Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';
    }

    /**
     * Disable/enable a user group
     *
     * @param integer              $intId
     * @param boolean              $blnVisible
     * @param Contao\DataContainer $dc
     *
     * @throws Contao\CoreBundle\Exception\AccessDeniedException
     */
    public function toggleGroupVisibility($intId, $blnVisible, Contao\DataContainer $dc=null)
    {
        // Set the ID and action
        Contao\Input::setGet('id', $intId);
        Contao\Input::setGet('act', 'toggle');

        if ($dc)
        {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (is_array($GLOBALS['TL_DCA']['tl_cookie_group']['config']['onload_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_cookie_group']['config']['onload_callback'] as $callback)
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
        if (!$this->User->hasAccess('tl_cookie_group::published', 'alexf'))
        {
            throw new Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish cookie group ID "' . $intId . '".');
        }

        $objRow = $this->Database->prepare("SELECT * FROM tl_cookie_group WHERE id=?")
            ->limit(1)
            ->execute($intId);

        if ($objRow->numRows < 1)
        {
            throw new Contao\CoreBundle\Exception\AccessDeniedException('Invalid cookie group ID "' . $intId . '".');
        }

        // Set the current record
        if ($dc)
        {
            $dc->activeRecord = $objRow;
        }

        $objVersions = new Contao\Versions('tl_cookie_group', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_cookie_group']['fields']['published']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_cookie_group']['fields']['published']['save_callback'] as $callback)
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
        $this->Database->prepare("UPDATE tl_cookie_group SET tstamp=$time, published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
            ->execute($intId);

        if ($dc)
        {
            $dc->activeRecord->tstamp = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (is_array($GLOBALS['TL_DCA']['tl_cookie_group']['config']['onsubmit_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_cookie_group']['config']['onsubmit_callback'] as $callback)
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

<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

namespace Oveleon\ContaoCookiebar;

use Contao\ContentElement;
use Contao\StringUtil;
use Contao\System;

class ContentCookiebar extends ContentElement
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ccb_opener_default';

    /**
     * Generate the content element
     */
    protected function compile()
    {
        System::loadLanguageFile('tl_cookiebar');

        $this->Template->href = 'javascript:;';
        $this->Template->attribute = ' onclick="cookiebar.show('.$this->prefillCookies.');"';
        $this->Template->rel = ' rel="noreferrer noopener"';
        $this->Template->link = $this->linkTitle ?: $GLOBALS['TL_LANG']['tl_cookiebar']['changePrivacyLabel'];
        $this->Template->linkTitle = '';

        if ($this->titleText)
        {
            $this->Template->linkTitle = StringUtil::specialchars($this->titleText);
        }

        // Unset the title attributes in the back end (see #6258)
        if (TL_MODE == 'BE')
        {
            $this->Template->title = '';
            $this->Template->linkTitle = '';
            $this->Template->attribute = '';
        }
    }
}

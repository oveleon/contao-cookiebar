<?php

declare(strict_types=1);

/*
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @author      Sebastian Zoglowek    <https://github.com/zoglo>
 * @copyright   Oveleon               <https://www.oveleon.de/>
 */

namespace Oveleon\ContaoCookiebar\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Image;
use Contao\System;

class CookieConfigCallbackListener
{
    use CookiebarTrait;

    /**
     * Add button for adding default script configurations
     */
    #[AsCallback(table: 'tl_cookie_config', target: 'fields.scriptConfig.xlabel')]
    public function selectScriptPreset(DataContainer $dc)
    {
        System::loadLanguageFile('tl_cookie');

        $key = $dc->activeRecord->type;
        $id  = 'script' . $dc->activeRecord->type;

        $xlabel  = vsprintf(' <a href="javascript:;" id="script_%s" title="%s" data-action="contao--scroll-offset#store" onclick="ace.edit(\'ctrl_%s_div\').setValue(Cookiebar.getConfigScript(\'%s\'))">%s</a><script>Cookiebar.issetConfigScript(\'%s\',document.getElementById(\'script_%s\'));</script>', [
            $id,
            $GLOBALS['TL_LANG']['tl_cookie']['scriptConfig_xlabel'] ?? '',
            $dc->field,
            $key,
            Image::getHtml('theme_import.svg', $GLOBALS['TL_LANG']['tl_cookie']['scriptConfig_xlabel']),
            $key,
            $id
        ]);

        $xlabel .= vsprintf(' <a href="javascript:;" id="docs_%s" title="%s" data-action="contao--scroll-offset#store" onclick="window.open(Cookiebar.getCookieDocs(\'%s\'), \'_blank\')">%s</a><script>Cookiebar.issetCookieDocs(\'%s\',document.getElementById(\'docs_%s\'));</script>', [
            $id,
            ($GLOBALS['TL_LANG']['tl_cookie']['scriptDocs_xlabel'] ?? ''),
            $key,
            Image::getHtml('show.svg', $GLOBALS['TL_LANG']['tl_cookie']['scriptConfig_xlabel']),
            $key,
            $id
        ]);

        return $xlabel;
    }

    /**
     * Overwrite vendor* field translation by type
     */
    #[AsCallback(table: 'tl_cookie_config', target: 'fields.vendorId.load')]
    public function overwriteTranslation(mixed $value, DataContainer $dc): mixed
    {
        return $this->setTranslationByType($value, $dc);
    }

    /**
     * Add host prefix for source URLs from the same origin
     */
    #[AsCallback(table: 'tl_cookie_config', target: 'fields.sourceUrl.save')]
    public function addHostPrefix(string $varValue): string
    {
        return $this->setHostPrefix($varValue);
    }
}

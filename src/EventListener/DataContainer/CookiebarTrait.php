<?php

namespace Oveleon\ContaoCookiebar\EventListener\DataContainer;

use Contao\Backend;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;

trait CookiebarTrait
{
    /**
     * Add host prefix for source URLs from the same origin
     */
    protected function setHostPrefix(string $varValue): string
    {
        if(!trim($varValue))
        {
            return $varValue;
        }

        if(
            (str_starts_with($varValue, 'http')) ||
            (str_starts_with($varValue, 'https')) ||
            (str_starts_with($varValue, 'www')) ||
            (str_starts_with($varValue, '//')) ||
            (str_starts_with($varValue, '{{'))
        )
        {
            return $varValue;
        }

        return '{{env::url}}/' . $varValue;
    }

    /**
     * Disable button when identifier is locked
     */
    protected function disableButtonOnLocked(array $row, ?string $href, string $label, string $title, ?string $icon, string $attributes): string
    {
        // Disable the button if the element is locked
        if ($row['identifier'] === 'lock')
        {
            return Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
        }

        return vsprintf('<a href="%s" title="%s"%s>%s</a> ', [
            Backend::addToUrl($href . '&amp;id=' . $row['id']),
            StringUtil::specialchars($title),
            $attributes,
            Image::getHtml($icon, $label)
        ]);
    }

    /**
     * Overwrite vendor* field translation by type
     */
    protected function setVendorTranslation(string $value, DataContainer $dc): string
    {
        System::loadLanguageFile($dc->table);

        $field = $dc->activeRecord->type . '_' . $dc->field;

        if($tl = $GLOBALS['TL_LANG'][$dc->table][$field])
        {
            $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['label'] = $tl;
        }

        return $value;
    }
}

<?php

namespace Oveleon\ContaoCookiebar\EventListener\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Oveleon\ContaoCookiebar\Cookiebar;

class PageCallbackListener
{
    /**
     * Cookiebar opener trigger CSS Class
     */
    const CLASS_TRIGGER = 'ccb-trigger';

    /**
     * Cookiebar prefill settings CSS Class
     */
    const CLASS_PREFILL = 'ccb-prefill';

    /**
     * Attach trigger CSS Class
     *
     * @Callback(table="tl_page", target="fields.cssClass.save")
     */
    public function onSaveCssClass(?string $value, DataContainer $dc): string
    {
        if($dc->activeRecord->triggerCookiebar && $dc->activeRecord->type === 'forward')
        {
            $value = $this->clearCssClasses($value);
            $value .= ' ' . self::CLASS_TRIGGER;

            if($dc->activeRecord->prefillCookies)
            {
                $value .= ' ' . self::CLASS_PREFILL;
            }

            return trim($value);
        }

        return $value ?? '';
    }

    /**
     * Remove trigger CSS class from value
     *
     * @Callback(table="tl_page", target="fields.cssClass.load")
     */
    public function clearCssClasses(?string $value): string
    {
        if(str_contains($value, self::CLASS_TRIGGER) || str_contains($value, self::CLASS_PREFILL))
        {
            return trim(preg_replace('#\s+#', ' ', str_replace([self::CLASS_TRIGGER, self::CLASS_PREFILL], '', $value)));
        }

        return $value ?? '';
    }

    /**
     * Return all cookiebar templates
     *
     * @Callback(table="tl_page", target="fields.cookiebarTemplate.options")
     */
    public function getCookiebarTemplates(): array
    {
        return Controller::getTemplateGroup('cookiebar_');
    }

    /**
     * Return all cookiebar templates
     *
     * @Callback(table="tl_page", target="fields.cookiebarConfig.options")
     */
    public function getCookiebarConfigurations(): array
    {
        return Cookiebar::getConfigurationList();
    }
}

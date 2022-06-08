<?php

namespace Oveleon\ContaoCookiebar\EventListener\DataContainer;

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
     * Clean up the CSS classes field
     *
     * @param $varValue
     * @param $dc
     * @return string
     */
    public function onLoadCssClass($varValue, $dc): string
    {
        return $this->clearCssClasses($varValue);
    }

    /**
     * Attach trigger CSS Class
     *
     * @param $varValue
     * @param $dc
     * @return string
     */
    public function onSaveCssClass($varValue, $dc): string
    {
        if($dc->activeRecord->triggerCookiebar && $dc->activeRecord->type === 'forward')
        {
            $varValue = $this->clearCssClasses($varValue);
            $varValue .= ' ' . self::CLASS_TRIGGER;

            if($dc->activeRecord->prefillCookies)
            {
                $varValue .= ' ' . self::CLASS_PREFILL;
            }

            return trim($varValue);
        }

        return $varValue ?? '';
    }

    /**
     * Remove trigger CSS class from value
     *
     * @param $varValue
     * @return string
     */
    private function clearCssClasses($varValue): string
    {
        if(strpos($varValue, self::CLASS_TRIGGER) !== false || strpos($varValue, self::CLASS_PREFILL) !== false)
        {
            return trim(preg_replace('#\s+#', ' ', str_replace([self::CLASS_TRIGGER, self::CLASS_PREFILL], '', $varValue)));
        }

        return $varValue ?? '';
    }
}

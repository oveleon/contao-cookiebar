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
use Contao\CoreBundle\Twig\Finder\FinderFactory;
use Contao\DataContainer;
use Oveleon\ContaoCookiebar\Cookiebar;

readonly class PageCallbackListener
{
    /**
     * Cookiebar opener trigger CSS Class
     */
    const string CLASS_TRIGGER = 'ccb-trigger';

    /**
     * Cookiebar prefill settings CSS Class
     */
    const string CLASS_PREFILL = 'ccb-prefill';

    public function __construct(private FinderFactory $finderFactory)
    {}

    /**
     * Attach trigger CSS Class
     */
    #[AsCallback(table: 'tl_page', target: 'fields.cssClass.save')]
    public function onSaveCssClass(string|null $value, DataContainer $dc): string
    {
        if ($dc->activeRecord->triggerCookiebar && $dc->activeRecord->type === 'forward')
        {
            $value = $this->clearCssClasses($value);
            $value .= ' ' . self::CLASS_TRIGGER;

            if ($dc->activeRecord->prefillCookies)
            {
                $value .= ' ' . self::CLASS_PREFILL;
            }

            return trim($value);
        }

        return $value ?? '';
    }

    /**
     * Remove trigger CSS class from value
     */
    #[AsCallback(table: 'tl_page', target: 'fields.cssClass.load')]
    public function clearCssClasses(string|null $value): string
    {
        if (str_contains((string) $value, self::CLASS_TRIGGER) || str_contains((string) $value, self::CLASS_PREFILL))
        {
            return trim((string) preg_replace('#\s+#', ' ', str_replace([self::CLASS_TRIGGER, self::CLASS_PREFILL], '', $value)));
        }

        return $value ?? '';
    }

    /**
     * Return all cookiebar templates
     */
    #[AsCallback(table: 'tl_page', target: 'fields.cookiebarTemplate.options')]
    public function getCookiebarTemplates(): array
    {
        return $this->finderFactory
            ->create()
            ->identifier('cookiebar/default')
            ->extension('html.twig')
            ->withVariants()
            ->asTemplateOptions()
        ;
    }

    /**
     * Return all cookiebar configurations
     */
    #[AsCallback(table: 'tl_page', target: 'fields.cookiebarConfig.options')]
    public function getCookiebarConfigurations(): array
    {
        return Cookiebar::getConfigurationList();
    }
}

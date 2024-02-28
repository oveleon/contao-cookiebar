<?php

declare(strict_types=1);

/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

namespace Oveleon\ContaoCookiebar\EventListener;

use Contao\FrontendTemplate;
use Contao\PageModel;
use Contao\System;
use Contao\StringUtil;
use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class InsertTagsListener
{
    private const SUPPORTED_TAGS = [
        'cookiebar'
    ];

    /**
     * @var ContaoFramework
     */
    private $framework;

    private RouterInterface $router;
    private RequestStack $requestStack;

    public function __construct(ContaoFramework $framework, RouterInterface $router, RequestStack $requestStack)
    {
        $this->framework = $framework;
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    /**
     * @return string|false
     */
    public function __invoke(string $tag, bool $useCache, $cacheValue, array $flags)
    {
        $elements = explode('::', $tag);
        $key = strtolower($elements[0]);

        if (\in_array($key, self::SUPPORTED_TAGS, true))
        {
            return $this->replaceCookiebarInsertTag($key, $elements, $flags);
        }

        return false;
    }

    private function replaceCookiebarInsertTag(string $insertTag, array $elements, array $flags): string
    {
        switch ($elements[1])
        {
            case 'show':
                $objTemplate = new FrontendTemplate('ccb_opener_default');

                $objTemplate->href = 'javascript:;';
                $objTemplate->rel = ' rel="noreferrer noopener"';
                $objTemplate->link = $elements[2] ?? ($GLOBALS['TL_LANG']['tl_cookiebar']['changePrivacyLabel'] ?? '');
                $objTemplate->linkTitle = StringUtil::specialchars($elements[3] ?? '');
                $objTemplate->attribute = ' onclick="cookiebar.show(' . (isset($elements[4]) && !$elements[4] ? 0 : 1) . ');"';

                return $objTemplate->parse();
        }

        return '';
    }
}

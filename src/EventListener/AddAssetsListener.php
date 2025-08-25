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

namespace Oveleon\ContaoCookiebar\EventListener;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\Asset\Packages;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[AsEventListener]
readonly class AddAssetsListener
{
    public function __construct(
        private ScopeMatcher $scopeMatcher,
        private Packages $package,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if ($this->scopeMatcher->isBackendMainRequest($event))
        {
            $GLOBALS['TL_JAVASCRIPT'][] = $this->package->getUrl('config.js', 'contao_cookiebar');
        }
    }
}

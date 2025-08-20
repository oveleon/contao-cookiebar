<?php

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
        private Packages     $package,
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

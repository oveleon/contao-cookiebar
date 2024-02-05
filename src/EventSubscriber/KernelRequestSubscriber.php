<?php

namespace Oveleon\ContaoCookiebar\EventSubscriber;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class KernelRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected ScopeMatcher $scopeMatcher
    ){}

    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => 'onKernelRequest'];
    }

    public function onKernelRequest(RequestEvent $e): void
    {
        $request = $e->getRequest();

        if ($this->scopeMatcher->isBackendRequest($request)) {
            $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaocookiebar/scripts/configPresets.min.js';
        }
    }
}

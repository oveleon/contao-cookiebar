<?php

declare(strict_types=1);

namespace Oveleon\ContaoCookiebar\EventListener;

use Contao\ContentModel;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Oveleon\ContaoCookiebar\Cookiebar;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class KernelRequestListener
{
    private ?string $rootPageBuffer = null;
    private mixed $rootPagePosition = '';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly TokenChecker        $tokenChecker
    )
    {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($this->rootPageBuffer === null && $request->attributes->has('pageModel')) {

            $pageModel = $request->attributes->get('pageModel');
            if ($pageModel instanceof PageModel) {

                $rootPageObject = PageModel::findByPk($pageModel->rootId);
                if ($rootPageObject instanceof PageModel) {
                    $this->prepareCookieBar($pageModel, $rootPageObject);
                }

            }

        }

        if ($request->attributes->has('contentModel')) {

            $contentModel = $request->attributes->get('contentModel');

            if (!$contentModel instanceof ContentModel) {
                $contentModel = ContentModel::findByPk($contentModel);
            }

        }

        if ($request->attributes->has('moduleModel')) {

            $moduleModel = $request->attributes->get('moduleModel');

            if (!$moduleModel instanceof ModuleModel) {
                $moduleModel = ModuleModel::findByPk($moduleModel);
            }

        }

    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();

        $response = $event->getResponse();
        $content = $response->getContent(false);

        $pageModel = $request->attributes->get('pageModel');
        if ($pageModel instanceof PageModel) {

            $controller = $request->attributes->get('_controller');
            // @TODO maybe better identifier that it is PageTemplate
            if ($controller === 'Contao\FrontendIndex::renderPage') {

                $content = match ($this->rootPagePosition) {
                    'bodyAboveContent' => preg_replace("/<body([^>]*)>(.*?)<\/body>/is", "<body$1>$this->rootPageBuffer$2</body>", $content),
                    default => str_replace("</body>", "$this->rootPageBuffer</body>", $content),
                };

                $response->setContent($content);

            }

        }

    }

    private function prepareCookieBar(PageModel $pageModel, PageModel $rootPageModel): void
    {
        $objConfig = Cookiebar::getConfigByPage($rootPageModel);
        if ($objConfig !== null) {

            $strHtml = Cookiebar::parseCookiebarTemplate($objConfig);

            if ($objConfig->scriptPosition === 'body') {
                $strHtml .= '<script src="bundles/contaocookiebar/scripts/cookiebar.min.js"></script>';
            } else {
                // @TODO better implementation
                $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaocookiebar/scripts/cookiebar.min.js|static';
            }

            $strHtml .= vsprintf("<script>var cookiebar = new ContaoCookiebar({configId:%s,pageId:%s,version:%s,lifetime:%s,token:'%s',doNotTrack:%s,currentPageId:%s,excludedPageIds:%s,cookies:%s,configs:%s,disableTracking:%s, texts:{acceptAndDisplay:'%s'}});</script>", [
                $objConfig->id,
                $objConfig->pageId,
                $objConfig->version,
                System::getContainer()->getParameter('contao_cookiebar.lifetime'),
                System::getContainer()->getParameter('contao_cookiebar.storage_key'),
                System::getContainer()->getParameter('contao_cookiebar.consider_dnt') ? 1 : 0,
                $pageModel->id,
                json_encode(StringUtil::deserialize($objConfig->excludePages)),
                json_encode(Cookiebar::validateCookies($objConfig)),
                json_encode(Cookiebar::validateGlobalConfigs($objConfig)),
                $this->tokenChecker->hasBackendUser() && !!$objConfig->disableTrackingWhileLoggedIn ? 1 : 0,
                $this->translator->trans('tl_cookiebar.acceptAndDisplayLabel', [], 'contao_default')
            ]);

            $this->rootPageBuffer = $strHtml;
            $this->rootPagePosition = $objConfig->position;

        }

    }

}
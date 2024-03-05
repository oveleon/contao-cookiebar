<?php

namespace Oveleon\ContaoCookiebar\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsFrontendModule(category:'application', template:'ccb_opener_default')]
class CookiebarOpenerController extends AbstractFrontendModuleController
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ){}

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        System::loadLanguageFile('tl_cookiebar');

        $template->href = 'javascript:;';
        $template->linkClasses = 'ccb-trigger' . ($model->prefillCookies ? ' ccb-prefill' : '');
        $template->rel = ' rel="noreferrer noopener"';
        $template->link = $model->linkTitle ?: $this->translator->trans('tl_cookiebar.changePrivacyLabel', [], 'contao_default');
        $template->linkTitle = '';

        if ($model->titleText)
        {
            $template->linkTitle = StringUtil::specialchars($model->titleText);
        }

        // Unset the title attributes in the back end (see #6258)
        if(System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request))
        {
            $template->title = '';
            $template->linkTitle = '';
        }

        return $template->getResponse();
    }
}

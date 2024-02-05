<?php

namespace Oveleon\ContaoCookiebar\Controller\ContentElement;

use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\ContentModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsContentElement(category:'links', template:'ccb_opener_default')]
class CookiebarOpenerController extends AbstractContentElementController
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ){}

    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        System::loadLanguageFile('tl_cookiebar');

        $template->href = 'javascript:;';
        $template->attribute = ' onclick="cookiebar.show('.$model->prefillCookies.');"';
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
            $template->attribute = '';
        }

        return $template->getResponse();
    }
}

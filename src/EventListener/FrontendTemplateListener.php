<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

namespace Oveleon\ContaoCookiebar\EventListener;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use Oveleon\ContaoCookiebar\Cookiebar;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Symfony\Contracts\Translation\TranslatorInterface;

// @TODO Listener can be removed
class FrontendTemplateListener
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly TokenChecker        $tokenChecker
    ){}

    /**
     * Output front end template
     */
    #[AsHook('outputFrontendTemplate')]
    public function onOutputFrontendTemplate(string $buffer, string $template): string
    {
        // @TODO remove
        return $buffer;
    }


    /**
     * Check content element and module templates to be modified
     */
    #[AsHook('getContentElement')]
    #[AsHook('getFrontendModule')]
    public function parseTemplates(Model $model, string $buffer): string
    {
        // @TODO remove
        return $buffer;
    }
}

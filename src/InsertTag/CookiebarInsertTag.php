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

namespace Oveleon\ContaoCookiebar\InsertTag;

use Contao\CoreBundle\DependencyInjection\Attribute\AsInsertTag;
use Contao\CoreBundle\InsertTag\Exception\InvalidInsertTagException;
use Contao\CoreBundle\InsertTag\InsertTagResult;
use Contao\CoreBundle\InsertTag\OutputType;
use Contao\CoreBundle\InsertTag\ResolvedInsertTag;
use Contao\CoreBundle\InsertTag\ResolvedParameters;
use Contao\CoreBundle\String\HtmlAttributes;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsInsertTag('cookiebar')]
readonly class CookiebarInsertTag
{
    public function __construct(private TranslatorInterface $translator)
    {}

    public function __invoke(ResolvedInsertTag $insertTag): InsertTagResult
    {
        if ($insertTag->getParameters()->get(0) === null)
        {
            throw new InvalidInsertTagException('Missing parameters for insert tag.');
        }

        $return = $this->replaceCookiebarInsertTag($insertTag->getParameters());

        return new InsertTagResult($return, OutputType::html);
    }

    private function replaceCookiebarInsertTag(ResolvedParameters $parameters): string
    {
        if ($parameters->get(0) !== 'show')
        {
            return '';
        }

        $label = $parameters->get(1) ?? $this->translator->trans('cookiebar.change_privacy_label', [], 'cookiebar');

        $attributes = (new HtmlAttributes())
            ->addClass(['ccb-trigger', ($parameters->get(3) === '0' ? '' : 'ccb-prefill')])
            ->set('href', 'javascript:;')
            ->set('rel', 'noreferrer noopener')
            ->setIfExists('title', $parameters->get(2))
        ;

        return \sprintf('<a%s>%s</a>', $attributes, $label);
    }
}

<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
 */

declare(strict_types=1);

namespace Oveleon\ContaoCookiebar;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ContaoCookiebar extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}

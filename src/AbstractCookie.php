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

namespace Oveleon\ContaoCookiebar;

/**
 * Base properties and resources of cookies and configs
 */
abstract class AbstractCookie
{
    /**
     * Position flag: Below the content within the body tag
     */
    const int POS_BELOW = 1;

    /**
     * Position flag: Above the content within the body tag
     */
    const int POS_ABOVE = 2;

    /**
     * Position flag: Within the head tag
     */
    const int POS_HEAD = 3;

    /**
     * Loading flag: Load only after confirmation
     */
    const int LOAD_CONFIRMED = 1;

    /**
     * Loading flag: Load only if not confirmed
     */
    const int LOAD_UNCONFIRMED = 2;

    /**
     * Loading flag: Load always
     */
    const int LOAD_ALWAYS = 3;

    /**
     * Resource scripts
     */
    protected array $resources = [];

    /**
     * Plain scripts
     */
    protected array $scripts = [];

    /**
     * Add a script
     */
    public function addScript(string $strScript, int|string $mode = self::LOAD_CONFIRMED, int|string $pos = self::POS_BELOW): void
    {
        $this->scripts[] = [
            'script'    => $strScript,
            'position'  => $pos,
            'mode'      => $mode
        ];
    }

    /**
     * Add a resource
     */
    public function addResource(string $strSrc, array $flags = null, int|string $mode = self::LOAD_CONFIRMED): void
    {
        $this->resources[] = [
            'src'   => $strSrc,
            'flags' => $flags,
            'mode'  => $mode
        ];
    }
}

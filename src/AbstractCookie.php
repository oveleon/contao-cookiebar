<?php
/**
 * This file is part of Oveleon Contao Cookiebar.
 *
 * @package     contao-cookiebar
 * @license     AGPL-3.0
 * @author      Daniele Sciannimanica <https://github.com/doishub>
 * @copyright   Oveleon <https://www.oveleon.de/>
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
    const POS_BELOW = 1;

    /**
     * Position flag: Above the content within the body tag
     */
    const POS_ABOVE = 2;

    /**
     * Position flag: Within the head tag
     */
    const POS_HEAD = 3;

    /**
     * Loading flag: Load only after confirmation
     */
    const LOAD_CONFIRMED = 1;

    /**
     * Loading flag: Load only if not confirmed
     */
    const LOAD_UNCONFIRMED = 2;

    /**
     * Loading flag: Load always
     */
    const LOAD_ALWAYS = 3;

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
     *
     * @param string $strScript
     * @param int $mode
     * @param int $pos
     */
    public function addScript(string $strScript, int $mode = self::LOAD_CONFIRMED, int $pos = self::POS_BELOW): void
    {
        $this->scripts[] = [
            'script'    => $strScript,
            'position'  => $pos,
            'mode'      => $mode
        ];
    }

    /**
     * Add a resource
     *
     * @param string $strSrc
     * @param array|null $flags
     * @param int $mode
     */
    public function addResource(string $strSrc, array $flags=null, int $mode = self::LOAD_CONFIRMED): void
    {
        $this->resources[] = [
            'src'   => $strSrc,
            'flags' => $flags,
            'mode'  => $mode
        ];
    }
}

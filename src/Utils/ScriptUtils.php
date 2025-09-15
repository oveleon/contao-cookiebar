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

namespace Oveleon\ContaoCookiebar\Utils;

/**
 * @interal
 */
class ScriptUtils
{
    private string|null $outputTemplate = null;
    private string|null $scriptCookieBar = null;
    private string|null $scriptConfigPattern = null;
    private array|null $scriptConfigValues = null;
    private string|null $globalJavaScript = null;

    public function getOutputTemplate(): string|null
    {
        return $this->outputTemplate;
    }

    public function setOutputTemplate(string|null $outputTemplate): void
    {
        $this->outputTemplate = $outputTemplate;
    }

    public function getScriptCookieBar(): string|null
    {
        return $this->scriptCookieBar;
    }

    public function setScriptCookieBar(string|null $scriptCookieBar): void
    {
        $this->scriptCookieBar = $scriptCookieBar;
    }

    public function getScriptConfigPattern(): string|null
    {
        return $this->scriptConfigPattern;
    }

    public function setScriptConfigPattern(string|null $scriptConfigPattern): void
    {
        $this->scriptConfigPattern = $scriptConfigPattern;
    }

    public function getScriptConfigValues(): array|null
    {
        return $this->scriptConfigValues;
    }

    public function setScriptConfigValues(array|null $scriptConfigValues): void
    {
        $this->scriptConfigValues = $scriptConfigValues;
    }

    public function getGlobalJavaScript(): string|null
    {
        return $this->globalJavaScript;
    }

    public function setGlobalJavaScript(string|null $globalJavaScript): void
    {
        $this->globalJavaScript = $globalJavaScript;
    }
}

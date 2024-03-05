<?php

declare(strict_types=1);

namespace Oveleon\ContaoCookiebar\Utils;

class ScriptUtils
{
    private ?string $outputTemplate = null;
    private ?string $scriptCookieBar = null;
    private ?string $scriptConfigPattern = null;
    private ?array $scriptConfigValues = null;
    private ?string $globalJavaScript = null;

    public function getOutputTemplate(): ?string
    {
        return $this->outputTemplate;
    }

    public function setOutputTemplate(?string $outputTemplate): void
    {
        $this->outputTemplate = $outputTemplate;
    }

    public function getScriptCookieBar(): ?string
    {
        return $this->scriptCookieBar;
    }

    public function setScriptCookieBar(?string $scriptCookieBar): void
    {
        $this->scriptCookieBar = $scriptCookieBar;
    }

    public function getScriptConfigPattern(): ?string
    {
        return $this->scriptConfigPattern;
    }

    public function setScriptConfigPattern(?string $scriptConfigPattern): void
    {
        $this->scriptConfigPattern = $scriptConfigPattern;
    }

    public function getScriptConfigValues(): ?array
    {
        return $this->scriptConfigValues;
    }

    public function setScriptConfigValues(?array $scriptConfigValues): void
    {
        $this->scriptConfigValues = $scriptConfigValues;
    }

    public function getGlobalJavaScript(): ?string
    {
        return $this->globalJavaScript;
    }

    public function setGlobalJavaScript(?string $globalJavaScript): void
    {
        $this->globalJavaScript = $globalJavaScript;
    }


}
- [Install](INSTALL.md)
- [Configuration (Basics)](BASICS.md)
    - [Create Configuration](CONFIGURATION.md)
    - [Create Group](GROUP.md)
    - [Create Cookie (Type)](COOKIE.md)
- [Module / Content-Element / Insert-tags](MOD_CE_MISC.md)
- [Styling & Customization](CUSTOMIZATION.md)
- [Extend iFrame-Types](EXTEND_IFRAME.md)
- [**Extend Cookie-Types**](EXTEND_TYPE.md)
- [Extended usage](EXTENDED_USAGE.md)
- [Content Security Policy](CONTENT_SECURITY_POLICY.md)

---

# Create own Cookie-Types
All cookie types are prepared by the PHP class `Cookie`. This class allows you to prepare the scripts to be processed by the JS plugin. See [Hooks](EXTEND_TYPE.md#hooks) for integration.

## Functions

### `addScript(string $strScript, int $mode, int $pos)`
Adds a script at the desired position (e.g. `<script>console.log(1);</script>`)
```php
public function addScript(string $strScript, int $mode = self::LOAD_CONFIRMED, int $pos = self::POS_BELOW): void
{
    $this->scripts[] = [
        'script'    => $strScript,
        'position'  => $pos,
        'mode'      => $mode
    ];
}
```

> **Attention:** The `$mode` parameter of the `addScript` function must be passed as a boolean value before Version 1.10. If true, the script is loaded only if the cookie is accepted.

Parameter | Description
---------- | -----------
`string $strScript` | Enables the integration of your own cookie types. (see Create own Cookie-Types)
`int $mode` | Defines the loading mode (see "Constants": Loading-Constants)
`int $pos` | Defines the position in HTML (see "Constants": Position-Constants)

<br/>

### `addResource(string $strSrc, array $flags, int $mode)`
Adds an external resource in the header area (e.g. `<script src="www.vendor.com/script.js" async></script>`)
```php
public function addResource(string $strSrc, array $flags=null, int $mode = self::LOAD_CONFIRMED): void
    {
        $this->resources[] = [
            'src'   => $strSrc,
            'flags' => $flags,
            'mode'  => $mode
        ];
    }
```

Parameter | Description
---------- | -----------
`string $strSrc` | The external URL
`array $flags` | Defines further tag attributes (e.g. `['async', 'defer']`)
`int $mode` | Defines the loading mode (see "Constants": Loading-Constants)

<br/>

## Constants
Position-Constants | Description
---------- | -----------
`POS_BELOW` | Below the content within the body tag
`POS_ABOVE` | Above the content within the body tag
`POS_HEAD` | Within the head tag

Loading-Constants | Description
---------- | -----------
`LOAD_CONFIRMED` | Load only after confirmation
`LOAD_UNCONFIRMED` | Load only if not confirmed
`LOAD_ALWAYS` | Load always

<br/>

# Hooks
Hook | Parameter | Description
---------- | ----------- | -----------
`compileCookieType` | `string $type`, `Cookie $objCookie` | Enables the integration of your own cookie types. (see Create own Cookie-Types)
`parseCookiebarTemplate` | `FrontendTemplate $objTemplate`, `$objConfig` | Is called before parsing the cookiebar template.

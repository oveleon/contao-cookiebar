# Hooks
Hook | Parameter | Description
---------- | ----------- | -----------
`compileCookieType` | `string $type`, `CookieHandler $objCookieHandler` | Enables the integration of your own cookie types. (see Create own Cookie-Types)
`parseCookiebarTemplate` | `FrontendTemplate $objTemplate`, `$objConfig` | Is called before parsing the cookiebar template.

# Create own Cookie-Types
All cookie types are prepared by the PHP class "CookieHandler". This class allows you to prepare the scripts to be processed by the JS plugin.

### Functions

#### `addScript(string $strScript, bool $confirmed, int $pos)`
Adds a script at the desired position (e.g. `<script>console.log(1);</script>`)

Parameter | Description
---------- | -----------
`string $strScript` | Enables the integration of your own cookie types. (see Create own Cookie-Types)
`bool $confirmed` | Defines whether the script should be output when the cookie is accepted or not
`int $pos` | Defines the position in HTML (see "Constants": Position-Constants)

#### `addResource(string $strSrc, array $flags, int $mode)`
Adds an external resource in the header area (e.g. `<script src="www.vendor.com/script.js" async></script>`)

Parameter | Description
---------- | -----------
`string $strSrc` | The external URL
`array $flags` | Defines further tag attributes (e.g. `['async', 'defer']`)
`int $mode` | Defines the loading mode (see "Constants": Loading-Constants)

### Constants
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

# Further links
- [Install & Configuration](CONFIGURATION.md)
- [Styling & Customization](CUSTOMIZATION.md)
- [Extended usage](EXTENDED_USAGE.md)

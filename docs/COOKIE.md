- [Install](INSTALL.md)
- [Configuration (Basics)](BASICS.md)
    - [Create Configuration](CONFIGURATION.md)
    - [Create Group](GROUP.md)
    - [**Create Cookie (Type)**](COOKIE.md)
- [Module & Content-Element](MOD_CE.md)
- [Styling & Customization](CUSTOMIZATION.md)
- [Extend iFrame-Types](EXTEND_IFRAME.md)
- [Extend Cookie-Types](EXTEND_TYPE.md)
- [Extended usage](EXTENDED_USAGE.md)

---

## Create new cookie type
Here you can set up the different services that should be available on the website.

### Cookie-Types
Cookie-Type | Description
---------- | -----------
`Info` | Defines a simple information about an externally set cookie. This type cannot integrate its own scripts.
`Custom (Script)` | This type allows you to integrate your own scripts. A special feature is that a script can also be included if the cookie was not accepted.
`Custom (Template)` | Here, as it is default in Contao, templates like `analytics_google` or your own can be selected and processed.
`Google Analytics` | This type integrates Google Analytics via the Tag Manager. Using Contao's own `analytic_google.html5` __is no longer necessary__! The integration takes place directly through this cookie type.
`Google Consent Mode` | This type integrates Google Consent Mode via the Tag Manager. Cookies of this type can be created multiple times depending on the consent mode.
`Facebook Pixel` | This type integrates a Facebook pixel.
`Matomo` | This type integrates Matomo. Using Contao's own `analytic_matomo.html5` __is no longer necessary__! The integration takes place directly through this cookie type.
`iFrame` | This type offers the possibility to block different sources which are embedded via iFrames. For default, the integration of `Youtube`, `Vimeo` and `Google Maps` is already available. See also [Create own iFrame-Types](EXTEND_IFRAME.md).

### iFrame-Types
Cookie-Type | Description
---------- | -----------
`YouTube` | Blocks YouTube videos that have been embedded using the YouTube content element.
`Vimeo` | Blocks Vimeo videos that have been embedded using the Vimeo content element.
`Google Maps` | Blocks all Google Maps, which was integrated via a HTML content element or module. The template `ce_html_googlemaps` or `mod_html_googlemaps` must be selected.

### Fields
Field | Description | Type
---------- | ----------- | -----------
`Title` | The title of the cookie. | All
`Cookie-Type` | The type of the cookie. [Below](CONFIGURATION.md#types) you will find a description of the individual types. | All
`Cookie-Token` | The technical name / token of the cookie (see all [Types](CONFIGURATION.md#types)). | All
`Retention period` | Define how long the cookie is stored or refer to the description of the provider. | All
`Provider` | The provider from whom the cookie is set. | All
`Description` | The description / purpose of the cookie. | All
`Preselect consent` | Activates the checkbox when opening the cookie bar for the first time. | All
`Published` | Defines whether the cookie is in active use. | All
`Source URL` | Allows the inclusion of external scripts in the HEAD-Tag. | Custom (Script)
`Source URL loading mode` | Defines when the source URL may be loaded. | Custom (Script)
`Source URL Parameter` | Enables the addition of further parameters for loading the external source URL. | Custom (Script)
`Script (confirmed)` | Script which is integrated after accepting the cookie. | Custom (Script)
`Script (unconfirmed)` | Script which is included if the cookie is not accepted. | Custom (Script)
`Script position` | Defines the position in HTML where the script should be included. This setting applies to both the accepted and the unaccepted script. | Custom (Script)
`Google Analytics ID` | Your Google Analytics ID / Container ID (Google Tag Manager). | Google Analytics
`Additional configuration` | Here you can specify additional parameters. You can load a predefined set of parameters by clicking the icon next to the title. | Google Analytics
`Facebook-ID` | Your Facebook ID. | Facebook Pixel
`Matomo-ID` | Your Matomo ID. | Matomo
`Matomo Server-URL` | Matomo Server-URL. | Matomo
`Global configuration` | A global configuration allows additional script bindings for different cookies, these can also be used across cookies. | Custom (Script), Custom (Template), Google Consent Mode
`Template (blocked)` | Here you can specify an individual template for blocked iFrame. | All iframe types
`Description (blocked)` | This description appears instead of the content element if it is blocked by missing cookies. | All iframe types

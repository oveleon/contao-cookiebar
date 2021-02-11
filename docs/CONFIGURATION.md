- [**Install & Configuration**](CONFIGURATION.md)
- [Styling & Customization](CUSTOMIZATION.md)
- [Extending your own modules](EXTENDING.md)
- [Extended usage](EXTENDED_USAGE.md)

---

# Install
```
$ composer require oveleon/contao-cookiebar
```

The cookie bar can also be installed via the Contao Manager. Just find the package and click on add. For more information, see [Contao Manager](https://docs.contao.org/manual/de/installation/erweiterungen-installieren/).

# Configuration
Once the package is installed, the Cookiebar menu item appears in the system palette in the main navigation of the backend. Here you can create and manage multiple configurations of a cookie bar. Afterwards it can be assigned to a ROOT page.

ℹ If you have already used a Contao `analytic_*` template, please make sure that you do not use it anymore!

## Create new configuration

Field | Description
---------- | -----------
`Title` | The internal title
`Title & Description` | A title and description which is displayed in the header of the cookie bar
`Info-Description` | Another way to provide information. Is displayed below the buttons
`Alignment` | Defines the orientation of the cookie bar
`Blocking` | Defines whether the cookie bar should block the use of the page
`Template` | Defines the template to be used (see [Styling & Customization](CUSTOMIZATION.md) for more information)
`Info-Links` | Here you can select several pages from the page picker, which are displayed in the footer of the cookie bar. (e.g. imprint, privacy policy)
`Exclude Pages` | Here you can select pages in which the cookie bar should not be displayed.
`Position` | Defines the position in the body tag
`Script position` | Defines where the Cookiebar scripts will be embedded
`Version` | Allows to call the cookie bar again, even if the visitor has already accepted it

ℹ After creating a configuration, cookie settings necessary for the system are automatically created. If you do not want to display system cookies, you can simply hide them.

## Create new cookie group
Now you can create additional cookie groups and thus create a meaningful separation between analysis cookies and others. A special feature is that groups can also be created without cookies and thus only act as a descriptive group.

Field | Description
---------- | -----------
`Group title` | A meaningful group title which describes the cookies in this group
`Description` | A description which provides further information about this group

## Create new cookie
Some types for cookies are already delivered: 
- Custom (Script)
- Custom (Template)
- Google Analytics
- Google Consent Mode (`>=1.8.0`)
- Facebook Pixel
- Matomo
- iFrame
  - YouTube
  - Vimeo
  - Google Maps
  - [add more...](EXTENDING.md#create-own-iframe-types)
  
To add your own cookie types see [Create own Cookie-Types](EXTENDING.md#create-own-cookie-types).

### Fields
Field | Description | Type
---------- | ----------- | -----------
`Title` | The title of the cookie. | All
`Cookie-Type` | The type of the cookie. [Below](CONFIGURATION.md#types) you will find a description of the individual types. | All
`Cookie-Token` | The technical name / token of the cookie (see all [Types](CONFIGURATION.md#types)). | All
`Cookie storage duration` | Define how long the cookie is stored or refer to the description of the provider. | All
`Provider` | The provider from whom the cookie is set. | All
`Description` | The description / purpose of the cookie. | All
`Published` | Defines whether the cookie is in active use. | All
`Source URL` | Allows the inclusion of external scripts in the HEAD-Tag. | Custom (Script)
`Loading-Mode of Source URL` | Defines when the source URL may be loaded. | Custom (Script)
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
 
### Types
Cookie-Type | Description
---------- | -----------
`Info` | Defines a simple information about an externally set cookie. This type cannot integrate its own scripts.
`Custom (Script)` | This type allows you to integrate your own scripts. A special feature is that a script can also be included if the cookie was not accepted.
`Custom (Template)` | Here, as it is default in Contao, templates like `analytics_google` or your own can be selected and processed.
`Google Analytics` | This type integrates Google Analytics via the Tag Manager. Using Contao's own `analytic_google.html5` __is no longer necessary__! The integration takes place directly through this cookie type.
`Google Consent Mode` | This type integrates Google Consent Mode via the Tag Manager. Cookies of this type can be created multiple times depending on the consent mode.
`Facebook Pixel` | This type integrates a Facebook pixel.
`Matomo` | This type integrates Matomo. Using Contao's own `analytic_matomo.html5` __is no longer necessary__! The integration takes place directly through this cookie type.
`iFrame` | This type offers the possibility to block different sources which are embedded via iFrames. For default, the integration of `Youtube`, `Vimeo` and `Google Maps` is already available. See also [Create own iFrame-Types](EXTENDING.md#create-own-iframe-types).

### iFrame-Types
Cookie-Type | Description
---------- | -----------
`YouTube` | Blocks YouTube videos that have been embedded using the YouTube content element.
`Vimeo` | Blocks Vimeo videos that have been embedded using the Vimeo content element.
`Google Maps` | Blocks all Google Maps, which was integrated via a HTML content element or module. The template `ce_html_googlemaps` or `mod_html_googlemaps` must be selected.

<br/>

# Module / Content-Element
In order to be able to open the cookie bar from all sides again and to give the visitor the possibility to change his settings, the Module / Content-Element `Cookiebar` is provided.

### Fields
Field | Description
---------- | -----------
`Link text` | The link text will be displayed instead of the target URL
`Link title` | The link title is added as `title` attribute in the HTML markup
`Prefill settings` | Activates the already selected cookies when opening the cookie bar

### Template
Template | Description
---------- | ----------
`ccb_opener_default.html5` | Returns the template, which is responsible for the output of the Cookiebar module / content element.

<br/>

# Basic configuration
Basic settings must be maintained via the `config/config.yml` file. 

ℹ The following values are set by default, they do not need to be added to the yml file again.

```yaml
contao_cookiebar:
  consider_dnt: true
  storage_key: ccb_contao_token
  page_templates:
    - fe_page
  iframe_types:
    youtube: 
      - ce_youtube
    vimeo: 
      - ce_vimeo
    googlemaps:
      - ce_html_googlemaps
      - mod_html_googlemaps
```

Parameter | Description
---------- | -----------
`consider_dnt` | Consider "Do not Track" browser setting.
`storage_key` | The key used for localStorage
`page_templates` | An array with page templates which should be considered.
`iframe_types.*` | An array of iFrame-Types and the corresponding templates. By customizing this array, any type can be added (see [Create own iFrame-Types](EXTENDING.md#create-own-iframe-types))
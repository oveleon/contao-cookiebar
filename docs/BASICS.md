- [Install](INSTALL.md)
- [**Configuration (Basics)**](BASICS.md)
    - [Create Configuration](CONFIGURATION.md)
    - [Create Group](GROUP.md)
    - [Create Cookie (Type)](COOKIE.md)
- [Module / Content-Element / Insert-tags](MOD_CE_MISC.md)
- [Styling & Customization](CUSTOMIZATION.md)
- [Extend iFrame-Types](EXTEND_IFRAME.md)
- [Extend Cookie-Types](EXTEND_TYPE.md)
- [Extended usage](EXTENDED_USAGE.md)

---

# Basic configuration
Basic settings must be maintained via the `config/config.yml` file. 

ℹ The following values are set by default, they do not need to be added to the YML file again.

```yaml
contao_cookiebar:
  consider_dnt: false
  anonymize_ip: false
  consent_log: false
  disable_focustrap: false
  lifetime: 63072000
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
`consider_dnt` | Consider "Do not Track" browser setting
`consent_log` | Enables/disables consent logging. With each action by the visitor, information about the made choice of cookies is stored.
`anonymize_ip` | Anonymizes the visitor's IP address for each log entry using [Symfony IP Address Anonymizer](https://symfony.com/blog/new-in-symfony-4-4-ip-address-anonymizer).
`disable_focustrap` | Can be used to disable the focus trap that was introduced in `1.17`.
`lifetime` | Time in seconds that specifies how long the cookie bar settings apply. If the time has expired, the cookie bar is displayed again. If 0 is passed, the cookie bar will never be displayed again automatically and can only be triggered via the version within the cookie bar configuration. (Default: `63072000` = 2 years)
`storage_key` | The key used for localStorage
`page_templates` | An array with page templates which should be considered. Since version `1.8.2` all templates which start with `fe_page_` are considered by default.
`iframe_types.*` | An array of iFrame-Types and the corresponding templates. By customizing this array, any type can be added (see [Create own iFrame-Types](EXTEND_IFRAME.md))

# Console Commands
The anonymization of all entries can be triggered via the console as follows:
```
vendor/bin/contao-console cookiebar:anonymizeip
```

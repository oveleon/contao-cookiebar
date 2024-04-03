- [Install](INSTALL.md)
- [Configuration (Basics)](BASICS.md)
    - [Create Configuration](CONFIGURATION.md)
    - [Create Group](GROUP.md)
    - [Create Cookie (Type)](COOKIE.md)
- [Module / Content-Element / Insert-tags](MOD_CE_MISC.md)
- [Styling & Customization](CUSTOMIZATION.md)
- [**Extend iFrame-Types**](EXTEND_IFRAME.md)
- [Extend Cookie-Types](EXTEND_TYPE.md)
- [Extended usage](EXTENDED_USAGE.md)
- [Content Security Policy](CONTENT_SECURITY_POLICY.md)

---

# Create own iFrame-Types
By expanding the `config/config.yml` file, you can add as many iFrame types as you want to respond to different vendors / services. 
Types for blocking Youtube, Vimeo and Google Maps iFrames are already delivered by default.

### Example of new types
Add a new type and the template to which you have to react:
```yaml
contao_cookiebar:
  iframe_types:
    vendortype: 
      - ce_html_vendortype
```
Now another option "vendortype" appears in the cookie type "iFrame" within the select field "iFrame types". Select this to block all iFrames with the template `ce_html_vendortype` until the cookie is accepted.

### Example of additional templates
If you want to supplement your own templates with an already existing iFrame type, these can also be considered.
```yaml
contao_cookiebar:
  iframe_types:
    googlemaps: 
      - ce_my_additional_google_template
      - content_element/html/html_googlemaps
```

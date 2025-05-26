- [Install](INSTALL.md)
- [Configuration (Basics)](BASICS.md)
    - [Create Configuration](CONFIGURATION.md)
    - [Create Group](GROUP.md)
    - [Create Cookie (Type)](COOKIE.md)
- [Module / Content-Element / Insert-tags](MOD_CE_MISC.md)
- [**Styling & Customization**](CUSTOMIZATION.md)
- [Extend iFrame-Types](EXTEND_IFRAME.md)
- [Extend Cookie-Types](EXTEND_TYPE.md)
- [Extended usage](EXTENDED_USAGE.md)
- [Content Security Policy](CONTENT_SECURITY_POLICY.md)

---

# Cookie bar Templates
There are already three templates for a different presentation of the cookie bar.

<img src="https://www.oveleon.de/share/github-assets/contao-cookiebar/cookiebar_example.jpg">

Template | Description
---------- | ----------
`cookiebar_default.html5` | Returns the cookie bar directly with an overview of the different cookie groups.
`cookiebar_default_deny.html5` | Also returns the cookie bar with an overview of the different cookie groups, including the “Deny all” button.
`cookiebar_simple.html5` | Returns the cookie bar without an overview of the different cookie groups and offers the possibility to display them via an additional button.

### Styling
A separate stylesheet is output for each template. These are made available via the template itself, and can be removed and or replaced by your own if required via the template editor. 

The `_cookiebar.scss` included in the package contains all default stylings, responsive settings as well as animations can be integrated into your own style sheet.

> However, we recommend to override style adjustments only in a separate CSS file or to take the `_cookiebar.scss` file as a basis.

# Blocked Content Elements
Template | Description
---------- | ----------
`ccb_element_blocker.html5` | Returns the template which is output instead of blocked content elements. All styles that apply here are defined directly in this template.

<div align="center">
    <img src="https://www.oveleon.de/share/github-assets/contao-cookiebar/content-element-blocked-1.png">
    <p><i>Example blocked content elements</i></p>
</div>

ℹ Further iFrame-Types can be added dynamically, see [Create own iFrame-Types](EXTEND_IFRAME.md).

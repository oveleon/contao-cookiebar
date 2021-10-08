- [Install](INSTALL.md)
- [Configuration (Basics)](BASICS.md)
    - [**Create Configuration**](CONFIGURATION.md)
    - [Create Group](GROUP.md)
    - [Create Cookie (Type)](COOKIE.md)
- [Module & Content-Element](MOD_CE.md)
- [Styling & Customization](CUSTOMIZATION.md)
- [Extend iFrame-Types](EXTEND_IFRAME.md)
- [Extend Cookie-Types](EXTEND_TYPE.md)
- [Extended usage](EXTENDED_USAGE.md)

---

## Create new configuration

Field | Description
---------- | -----------
`Title` | The internal title
`Title & Description` | A title and description which is displayed in the header of the cookie bar
`Info-Description` | Another way to provide information. Is displayed below the buttons
`Alignment` | Defines the orientation of the cookie bar
`Blocking` | Defines whether the cookie bar should block the use of the page
`Button color scheme` | Defines the color scheme of the buttons
`Template` | Defines the template to be used (see [Styling & Customization](CUSTOMIZATION.md) for more information)
`Info-Links` | Here you can select several pages from the page picker, which are displayed in the footer of the cookie bar. (e.g. imprint, privacy policy)
`Exclude Pages` | Here you can select pages in which the cookie bar should not be displayed.
`Create essential cooking group` | Defines the language in which the essential cookies are created. If necessary, this group can be hidden via the eye symbol.
`Position` | Defines the position in the body tag
`Script position` | Defines where the Cookiebar scripts will be embedded
`Version` | Allows to call the cookie bar again, even if the visitor has already accepted it

ℹ After creating a configuration, cookie settings necessary for the system are automatically created. If you do not want to display system cookies, you can simply hide them.

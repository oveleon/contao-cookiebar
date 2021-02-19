- [**Install**](INSTALL.md)
- [Configuration (Basics)](BASICS.md)
    - [Create Configuration](CONFIGURATION.md)
    - [Create Group](GROUP.md)
    - [Create Cookie (Type)](COOKIE.md)
- [Module & Content-Element](MOD_CE.md)
- [Styling & Customization](CUSTOMIZATION.md)
- [Extend iFrame-Types](EXTEND_IFRAME.md)
- [Extend Cookie-Types](EXTEND_TYPE.md)
- [Extended usage](EXTENDED_USAGE.md)

---

# Install
```
$ composer require oveleon/contao-cookiebar
```

The cookie bar can also be installed via the Contao Manager. Just find the package and click on add. For more information, see [Contao Manager](https://docs.contao.org/manual/de/installation/erweiterungen-installieren/).

Once the package is installed, the Cookiebar menu item appears in the system palette in the main navigation of the backend. Here you can create and manage multiple [configurations](CONFIGURATION.md) of a cookie bar. Afterwards it can be assigned to a ROOT page.

ℹ If you have already used a Contao `analytic_*` template, please make sure that you do not use it anymore!
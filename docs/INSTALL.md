- [**Install**](INSTALL.md)
- [Configuration (Basics)](BASICS.md)
    - [Create Configuration](CONFIGURATION.md)
    - [Create Group](GROUP.md)
    - [Create Cookie (Type)](COOKIE.md)
- [Module / Content-Element / Insert-tags](MOD_CE_MISC.md)
- [Styling & Customization](CUSTOMIZATION.md)
- [Extend iFrame-Types](EXTEND_IFRAME.md)
- [Extend Cookie-Types](EXTEND_TYPE.md)
- [Extended usage](EXTENDED_USAGE.md)
- [Content Security Policy](CONTENT_SECURITY_POLICY.md)

---

# Install
```
$ composer require oveleon/contao-cookiebar
```

The cookie bar can also be installed via the Contao Manager. Just find the package and click on add. For more information, see [Contao Manager](https://docs.contao.org/manual/en/installation/install-extensions/).

Once the package is installed, the cookie bar menu item appears in the system palette in the main navigation of the backend. Here you can create and manage multiple [configurations](CONFIGURATION.md) of a cookie bar. 

Once done, a configuration can be assigned to a ROOT page to activate the respective cookie bar.

ℹ If you have already used a Contao `analytic_*` template, please make sure that you do not use it anymore!
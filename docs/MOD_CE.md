- [Install](INSTALL.md)
- [Configuration (Basics)](BASICS.md)
    - [Create Configuration](CONFIGURATION.md)
    - [Create Group](GROUP.md)
    - [Create Cookie (Type)](COOKIE.md)
- [**Module & Content-Element**](MOD_CE.md)
- [Styling & Customization](CUSTOMIZATION.md)
- [Extend iFrame-Types](EXTEND_IFRAME.md)
- [Extend Cookie-Types](EXTEND_TYPE.md)
- [Extended usage](EXTENDED_USAGE.md)

---

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

## Create your own links to open the cookie bar 
The following script can be used to reopen the cookie bar:
```js
cookiebar.show(true); // true = Activates the already confirmed cookie checkboxes
```

The link could look like this:
```html
<a href="javascript:cookiebar.show(true);">Privacy settings</a>
```

See also "[Extended usage](https://github.com/oveleon/contao-cookiebar/blob/master/docs/EXTENDED_USAGE.md)".

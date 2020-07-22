# Cookiebar Templates
There are already two templates for a different presentation of the cookibar. 

Template | Description
---------- | ----------
`cookiebar_default.html5` | Returns the cookie bar directly with an overview of the different cookie groups.
`cookiebar_simple.html5` | Returns the cookie bar without an overview of the different cookie groups and offers the possibility to display them via an additional button.

<p align="center">
    <img src="https://www.oveleon.de/share/github-assets/contao-cookiebar/cookiebar.png">
    <small><i>cookiebar_default.html5</i></small>
</p>

### Styling
A separate stylesheet is output for each template. These are made available via the template itself, and can be removed and or replaced by your own if required via the template editor. The `_cookiebar.scss` included in the package contains all standard stylings, responsive settings as well as animations and can be integrated into your own style sheet.

# Blocked Content Element
Template | Description
---------- | ----------
`ccb_element_blocker.html5` | Returns the template which is output instead of blocked content elements.

<p align="center">
    <img src="https://www.oveleon.de/share/github-assets/contao-cookiebar/content-element-blocked.png">
    <small><i>Example blocked content elements</i></small>
</p>

# Module
Template | Description
---------- | ----------
`ccb_opener_default.html5` | Returns the template, which is responsible for the output of the cookiebar module.

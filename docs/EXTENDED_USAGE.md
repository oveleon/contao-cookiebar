# JavaScript
```javascript
// Return instance of cookiebar
cookiebar.get();

// Return current cookie information
cookiebar.getStorage();

// Check if a cookie was accepted by id or token
cookiebar.issetCookie(1);
cookiebar.issetCookie('ga');

// Displays the cookie bar (restore: Activates the already confirmed cookie checkboxes)
cookiebar.show(restore: false);

// Hide the cookie bar
cookiebar.hide();
```

# PHP
```php
// Returns the complete configuration of a cookie bar
Cookiebar::getConfig(int $configId, $objMeta=null);
Cookiebar::getConfigByPage(PageModel|int $varPage);
```

# Controller
Route | GET-Parameter | Description
---------- | ----------- | -----------
`/cookiebar/delete` | `tokens` | Deletes all given cookies based on their token
`/cookiebar/log` | `configId`,`version` | Creates a new log entry
`/cookiebar/block/[id]` | `redirect` | Block-Page for iFrames

<br/>

# Further links
- [Install & Configuration](CONFIGURATION.md)
- [Styling & Customization](CUSTOMIZATION.md)
- [Extending your own modules](EXTENDING.md)


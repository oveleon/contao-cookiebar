# PHP
```php
// Check if a cookie was accepted by token or id
if(Cookiebar::issetCookie('_ga')) {...}
if(Cookiebar::issetCookie(1))  {...}

// Read current cookie information
Cookiebar::getCookie();

// Returns the complete configuration of a cookie bar
Cookiebar::getConfig(int $configId, $objMeta=null);
Cookiebar::getConfigByPage(PageModel|int $varPage);
```

# JavaScript
```javascript
// Return instance of cookiebar
cookiebar.get();

// Return current cookie information
cookiebar.getCookie();

// Displays the cookie bar (Prefill: Activates the already confirmed cookies)
cookiebar.show(prefill: false);

// Hide the cookie bar
cookiebar.hide();
```

# Controller
Route | GET-Parameter | Description
---------- | ----------- | -----------
`/cookiebar/save` | `configId`, `pageId`, `version`, `cookies` | Save a full set of cookies
`/cookiebar/push/[id]` | `configId` | Push cookie id to current set of cookies
`/cookiebar/isset/[id/token]` | `pageId` | Check whether a cookie was accepted based on the ID or the token


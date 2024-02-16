- [Install](INSTALL.md)
- [Configuration (Basics)](BASICS.md)
    - [Create Configuration](CONFIGURATION.md)
    - [Create Group](GROUP.md)
    - [Create Cookie (Type)](COOKIE.md)
- [Module / Content-Element / Insert-tags](MOD_CE_MISC.md)
- [Styling & Customization](CUSTOMIZATION.md)
- [Extend iFrame-Types](EXTEND_IFRAME.md)
- [Extend Cookie-Types](EXTEND_TYPE.md)
- [**Extended usage**](EXTENDED_USAGE.md)

---

# JavaScript
```javascript
// Return instance of cookie bar
cookiebar.get();

// Return current cookie information
cookiebar.getStorage();

// Check if a cookie was accepted by id or token
// - [int|string] cookieTypeIdOrToken: The ID of the cookie type or cookie token to be queried
cookiebar.issetCookie(1);
cookiebar.issetCookie('ga');

// Displays the cookie bar
// - [bool] restore (default: false): Activates the already confirmed cookie checkboxes
cookiebar.show(true);

// Hide the cookie bar
cookiebar.hide();

// Consider own scripts via CookieId and callback method
// - [int] cookieTypeId: The cookie type ID to be listened to
// - [function] callbackMethod: The function to be executed once the cookie type is accepted
// - [object] placeholderOptions (optional): Placeholder options for displaying own content
cookiebar.addModule(1, callbackMethod [, placeholderOptions]);

// In addition to the method mentioned above, the loading status of resources that are loaded 
// into the HEAD area via a cookie type (e.g. source URL) can be checked using the following method.
// This is necessary, for example, for scripts that depend on resources that are loaded asynchronously.
// - [int] cookieTypeId: The cookie type ID to be listened to
// - [function] callbackMethod: The function to be executed once the resource is loaded
cookiebar.onResourceLoaded(1, callbackMethod);

// Custom Events
window.addEventListener('cookiebar_init', function (e) {
  console.log('on init', e.detail);
}, false);

window.addEventListener('cookiebar_save', function (e) {
  console.log('on save', e.detail);
}, false);
```

### Example of the `addModule` method
Sometimes other extensions include external scripts, which must also be considered by the cookie bar. Let's assume that the initialization of a __Google Map__ is controlled by the __API__ instead of an iFrame.

__Add module__
```javascript
document.addEventListener("DOMContentLoaded", function() {
    cookiebar.addModule(cookieIdOfGoogleMaps, myCallbackMethodWithInitialization);
});
```

__Callback method__
```javascript
function myCallbackMethodWithInitialization(){
    // Initialization code
}
```

Furthermore, it is also possible to output a message for these scripts if the cookie has not yet been confirmed (like blocked content elements).
```javascript
document.addEventListener("DOMContentLoaded", function() {
    cookiebar.addModule(cookieIdOfGoogleMaps, myCallbackMethodWithInitialization, {
        selector: '#element',           // [required: string, HTMLElement] Defines the element in which the message is output
        message: 'Your text',           // [optional: string] The text to be displayed
        button: {                       // [optional: object]
            show: true,                 // [required: bool]   Extends the output by a confirmation button,
            text: 'Custom button text', // [optional: string] Button text
            type: 'button',             // [optional: string] Button type
            classes: 'first second'     // [optional: string] Own CSS classes for the button separated by spaces
        }
    });
});
```


# PHP
```php
// Returns the configuration instance of a cookie bar
Cookiebar::getConfig(int $configId, $objMeta=null);
Cookiebar::getConfigByPage(PageModel|int $varPage);
```

# Controller
Route | GET-Parameter | Description
---------- | ----------- | -----------
`/cookiebar/delete` | `tokens` | Deletes all given cookies based on their token
`/cookiebar/log` | `configId`,`version` | Creates a new log entry
`/cookiebar/block/[id]` | `redirect` | Block-Page for iFrames

- [Install & Configuration](CONFIGURATION.md)
- [Styling & Customization](CUSTOMIZATION.md)
- [Extending your own modules](EXTENDING.md)
- [**Extended usage**](EXTENDED_USAGE.md)

---

# JavaScript
```javascript
// Return instance of cookiebar
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
        selector: '#element',           // [required] Defines the element selector in which the message is output
        message: 'Your text',           // [required] The text to be displayed
        button: {                       
            show: true,                 // Extends the output by a confirmation button,
            text: 'Custom button text', // Button text
            type: 'button',             // Button type
            classes: 'first second'     // Own CSS classes for the button separated by spaces
        }
    });
});
```

ℹ Attention: Cookies of the type "Info" are not considered!

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
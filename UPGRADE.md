# Update from version 1.x to 2

### First things first
Before installing the latest Cookiebar version (2.0), make sure that the last version (1.x) of the Cookiebar was previously installed to fully pass all previous migrations.

### Restructuring of the bundle
- Moving classes into the Symfony structure
- Namespaces have changed
- Removing deprecations
- The Cookiebar module was converted into a FragmentController
- The Cookiebar content element was converted into a FragmentController

__Classes__
- The `CookieHandler` class has renamed to `Cookie`
- The `CookieConfig` class has renamed to `GlobalConfig`

__Models__
- The `CookieConfigModel` model has renamed to `GlobalConfigModel`

__Methods__
- The `blockAction()` method has been renamed to `block()` in `CookiebarController` class
- The `prepareAction()` method has been renamed to `execute()` in `CookiebarController` class
- The `compileGoogleConsentMode()` method has been renamed to `addGoogleConsentMode()` in `GlobalConfig` class
- The `compileTagManager()` method has been renamed to `addTagManager()` in `GlobalConfig` class
- The `compileScript()` method has been renamed to `addCustomScript()` in `GlobalConfig` class
- The `compileScript()` method has been renamed to `addCustomScript()` in `Cookie` class
- The `compileTemplate()` method has been renamed to `addCustomTemplate()` in `Cookie` class
- The `compileGoogleAnalytics()` method has been renamed to `addGoogleAnalytics()` in `Cookie` class
- The `compileGoogleConsentMode()` method has been renamed to `addGoogleConsentMode()` in `Cookie` class
- The `compileFacebookPixel()` method has been renamed to `addFacebookPixel()` in `Cookie` class
- The `compileMatomo()` method has been renamed to `addMatomo()` in `Cookie` class
- The `compileMatomoTagManager()` method has been renamed to `addMatomoTagManager()` in `Cookie` class
- The `compileEtracker()` method has been renamed to `addEtracker()` in `Cookie` class

__Hooks__
- The `compileCookieType` hook has been renamed to `addCookieType` in `Cookie` class
- The `compileCookieConfigType` hook has been renamed to `addGlobalConfigType` in `GlobalConfig` class

### New requirements
- Contao 4.13 / Contao 5.0
- PHP 8.1
- Symfony 5.4 / Symfony 6.0

### New features
- Tracking can be disabled while logged in to the backend (New checkbox in the configuration)

### Adjustments to the services
- The integration of the Google Tag Manager via the global configuration has changed

### Further adjustments
- The appearance was slightly adjusted (CSS)

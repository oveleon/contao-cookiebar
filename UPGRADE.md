# Update from version 1.x to 2

### First things first
Before installing the latest Cookiebar version (2.0), make sure that the last version (1.x) of the Cookiebar was previously installed to fully pass all previous migrations.

### Restructuring of the bundle
- Moving classes into the Symfony structure
- Namespaces have changed
- Removing deprecations
- The Cookiebar module was converted into a FragmentController
- The Cookiebar content element was converted into a FragmentController
- The `blockAction()` method has been renamed to `block()`
- The `prepareAction()` method has been renamed to `execute()`
- The `CookieHandler` class has renamed to `Cookie`
- The `CookieConfig` class has renamed to `GlobalConfig`
- The `CookieConfigModel` model has renamed to `GlobalConfigModel`

### New requirements
- Contao 4.13 / Contao 5.0
- PHP 8.1
- Symfony 5.4 / Symfony 6.0

### New features
- Tracking can be disabled while logged in to the backend (New checkbox in the configuration)

### Customizations to services
- The integration of the Google Tag Manager via the global configuration has changed

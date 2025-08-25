const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('./public/')
    .setPublicPath(Encore.isDevServer() ? '/public/' : '/bundles/contaocookiebar/')
    .setManifestKeyPrefix('')

    .cleanupOutputBeforeBuild((options) => {
        options.keep = (filename) => filename.startsWith('images/');
    })
    .disableSingleRuntimeChunk()
    .enableBuildNotifications()

    .addEntry('config', './assets/scripts/config-presets.js')
    .addEntry('cookiebar', './assets/scripts/cookiebar.js')

    .addEntry('default', './assets/default.js')
    .addEntry('simple', './assets/simple.js')

    .enablePostCssLoader()
    .enableVersioning(Encore.isProduction())

    .configureDevServerOptions((options) => Object.assign({}, options, {
        static: false,
        hot: true,
        liveReload: true,
        allowedHosts: 'all',
        watchFiles: ['assets/**/*', 'contao/**/*'],
        client: {
            overlay: false
        }
    }))
;

module.exports = Encore.getWebpackConfig();

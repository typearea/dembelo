var Encore = require('@symfony/webpack-encore');

Encore
// the project directory where all compiled assets will be stored
    .setOutputPath('web/build/')

    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')

    // will create web/build/app.js and public/build/app.css
    .addEntry('js/dembelo', './src/DembeloMain/Resources/public/js/dembelo.js')
    .addEntry('js/modal', './src/DembeloMain/Resources/public/js/modal.js')
    .addEntry('js/navigation', './src/DembeloMain/Resources/public/js/navigation.js')
    .addEntry('js/toolbar', './src/DembeloMain/Resources/public/js/toolbar.js')
    .addEntry('js/touch', './src/DembeloMain/Resources/public/js/touch.js')

    .addEntry('js/libs/require', './vendor/etdsolutions/requirejs/require.js')

    .addEntry('images/share-facebook', './src/DembeloMain/Resources/public/images/share-facebook.png')
    .addEntry('images/cc-by-sa', './src/DembeloMain/Resources/public/images/cc-by-sa.png')

    .addStyleEntry('css/dembelo', './src/DembeloMain/Resources/public/css/dembelo.scss')

    // allow sass/scss files to be processed
    .enableSassLoader()

    // allow legacy applications to use $/jQuery as a global variable
    .autoProvidejQuery()

    .enableSourceMaps(!Encore.isProduction())

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // show OS notifications when builds finish/fail
    .enableBuildNotifications()

// create hashed filenames (e.g. app.abc123.css)
// .enableVersioning()
;

// export the final configuration
module.exports = Encore.getWebpackConfig();
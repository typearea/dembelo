var Encore = require('@symfony/webpack-encore');

Encore
// the project directory where all compiled assets will be stored
    .setOutputPath('web/build/')

    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')

    // will create web/build/app.js and public/build/app.css
    .addEntry('dembelo', './src/DembeloMain/Resources/public/js/dembelo.js')
    .addEntry('modal', './src/DembeloMain/Resources/public/js/modal.js')
    .addEntry('navigation', './src/DembeloMain/Resources/public/js/navigation.js')
    .addEntry('toolbar', './src/DembeloMain/Resources/public/js/toolbar.js')
    .addEntry('touch', './src/DembeloMain/Resources/public/js/touch.js')

    .addEntry('images/share-facebook', './src/DembeloMain/Resources/public/images/share-facebook.png')
    .addEntry('images/cc-by-sa', './src/DembeloMain/Resources/public/images/cc-by-sa.png')

    .addStyleEntry('css', './src/DembeloMain/Resources/public/css/dembelo.scss')

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
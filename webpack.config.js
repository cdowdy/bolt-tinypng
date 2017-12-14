const Encore = require('@symfony/webpack-encore');
// const CopyWebpackPlugin = require('copy-webpack-plugin');
// const path = require('path');



Encore
    // directory where all compiled assets will be stored
    .setOutputPath('web/build/')

    // what's the public path to this directory (relative to your project's document root dir)
    .setPublicPath('/')

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // will output as web/build/app.js
    .addEntry('tinypng.optimize', './assets/js/main.js')

    .enableVueLoader()

    // will output as web/build/global.css
    // .addStyleEntry('tinypng.styles.vue', './assets/scss/googlephotos.scss')

    // allow sass/scss files to be processed
    // .enableSassLoader(function(sassOptions) {}, {
    //     resolveUrlLoader: false
    // })

    // allow legacy applications to use $/jQuery as a global variable
    // .autoProvidejQuery()

    // enable the copywebpack plugin
    // .addPlugin(new CopyWebpackPlugin([{
    //     from: './web/build/',
    //     to: '../../../../public/extensions/vendor/cdowdy/tinypng'
        // D:\Sites\bolt-extensions\extensions\vendor\cdowdy\tinypng\web\build\tinypng.optimize.vue.js
        // D:\Sites\bolt-extensions\public\extensions\vendor\cdowdy\tinypng\tinypng.optimize.vue.js

    // }]))

    .enableSourceMaps(!Encore.isProduction())

    // create hashed filenames (e.g. app.abc123.css)
    // .enableVersioning()
;

// export the final configuration
module.exports = Encore.getWebpackConfig();
const Encore = require('@symfony/webpack-encore');
const webpack = require('webpack');

Encore
  .setOutputPath('web/build/')
  .setPublicPath('/build')
  .cleanupOutputBeforeBuild()
  .addEntry('app', './assets/js/main.js')
  .addStyleEntry('global', './assets/css/main.scss')
  .enableSassLoader()
  // .autoProvidejQuery()
  .enableSourceMaps(!Encore.isProduction())
  // .enableVersioning()
  .addPlugin(new webpack.ProvidePlugin({
    $: 'jquery',
    jQuery: 'jquery',
    'window.jQuery': 'jquery',
    Popper: ['popper.js', 'default'],
  }))
;

// export the final configuration
module.exports = Encore.getWebpackConfig();

const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js(['resources/js/app.js'], 'public/js/app.js').vue()
  .sass('resources/sass/app.scss', 'public/css/app.css')
  .js(['resources/js/nova.js'], 'public/js/nova.js').vue()
  .sass('resources/sass/nova/global.scss', 'public/css/nova.css');

mix.disableNotifications();

if (mix.inProduction()) {
    mix.version();
}

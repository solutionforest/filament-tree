const mix = require('laravel-mix');
const tailwindcss = require('tailwindcss');

mix.setPublicPath('dist')
mix.setResourceRoot('resources')
mix.sourceMaps()

mix
    .postCss('resources/css/filament-tree.css', 'dist', [
        tailwindcss('tailwind.config.js'),
    ])
    .minify('dist/filament-tree.css')
    .version();

mix.disableSuccessNotifications()

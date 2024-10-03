// webpack.mix.js

let mix = require('laravel-mix');

// Tailwind
require('mix-tailwindcss');

mix.js('js/qrmanager.js', '../dist').setPublicPath('../dist');

mix.sass('scss/main.scss', '../dist/css/qrmanager.css')
   .tailwind('./tailwind.config.js');
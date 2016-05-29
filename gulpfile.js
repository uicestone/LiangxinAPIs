var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {

    // quiz style
    mix.styles([
        '../../../public/packages/bootstrap/dist/css/bootstrap.min.css',
        '../../../public/packages/font-awesome/css/font-awesome.min.css',
        'md-font.css',
        'quiz.css'
    ], 'public/assets/css/quiz.css');

    // quiz JavaScript
    mix.scripts([
        '../../../public/packages/angular/angular.min.js',
        '../../../public/packages/angular-route/angular-route.min.js',
        '../../../public/packages/angular-resource/angular-resource.min.js',
        '../../../public/packages/angular-bootstrap/ui-bootstrap-tpls.min.js',
        'quiz/services.js',
        'quiz/app.js'
    ], 'public/assets/js/quiz.js');

    // 版本化所有打包后的CSS和JS
    mix.version([
        'assets/css/quiz.css', 'assets/js/quiz.js'
    ]);

    // 复制CSS中用到的资源相对路径
    mix.copy('public/packages/font-awesome/fonts', 'public/build/assets/fonts');
    mix.copy('resources/assets/css/mdfonts', 'public/build/assets/css/mdfonts');
    mix.copy('resources/assets/html', 'public/assets/html');

});

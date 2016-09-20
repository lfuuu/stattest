<?php

namespace app\assets;

use yii\web\AssetBundle;
use app\classes\BaseView;

class SwaggerUiAsset extends AssetBundle
{
    public $sourcePath = '@bower/swagger-ui/dist';
    public $basePath = '@webroot';

    public $js = [
        'lib/jquery-1.8.0.min.js',
        'lib/jquery.slideto.min.js',
        'lib/jquery.wiggle.min.js',
        'lib/jquery.ba-bbq.min.js',
        'lib/handlebars-4.0.5.js',
        'lib/js-yaml.min.js',
        'lib/lodash.min.js',
        'lib/backbone-min.js',
        'swagger-ui.js',
        'lib/highlight.9.1.0.pack.js',
        'lib/highlight.9.1.0.pack_extended.js',
        'lib/jsoneditor.min.js',
        'lib/marked.js',
        'lib/swagger-oauth.js',
        'lang/translator.js',
        'lang/ru.js'
    ];

    public $css = [
        ['css/typography.css', 'media' => 'screen', 'type' => 'text/css'],
        ['css/reset.css', 'media' => 'screen', 'type' => 'text/css'],
        ['css/screen.css', 'media' => 'screen', 'type' => 'text/css'],
        ['css/reset.css', 'media' => 'print', 'type' => 'text/css'],
        ['css/print.css', 'media' => 'print', 'type' => 'text/css'],
    ];

    public $jsOptions = [
        'position' => BaseView::POS_HEAD,
    ];

}
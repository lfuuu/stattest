<?php
namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'lib/select2/select2.css',
        'css/themes/base/jquery-ui.css',
        'bootstrap/css/bootstrap.min.css',
        'main.css',
        'css/site.css',
    ];

    public $js = [
        'js/script.js',
        'js/jquery.js',
        'js/ui/jquery-ui.custom.js',
        'js/optools.js',
        'js/statlib/main.js',
        'js/jquery.meio.mask.min.js',
        'lib/select2/select2.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}

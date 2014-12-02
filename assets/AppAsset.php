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
        'css/themes/base/jquery-ui.css',
        'main.css',
        'css/site.css',
        'css/grid/grid.css',
    ];

    public $js = [
        'lib/meiomask.min.js',
        'js/ui/jquery-ui.custom.js',
        'js/script.js',
        'js/optools.js',
        'js/statlib/main.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'kartik\select2\Select2Asset'
    ];
}

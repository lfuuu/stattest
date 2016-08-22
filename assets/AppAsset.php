<?php
namespace app\assets;

use yii\web\AssetBundle;
use app\classes\BaseView;

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
        'css/flags/flags.css',
    ];

    public $js = [
        'lib/meiomask.min.js',
        'js/ui/jquery-ui.custom.js',
        'js/script.js',
        'js/optools.js',
        'js/statlib/main.js',
        'js/js.cookie.js',
    ];

    public $jsOptions = [
        'position' => BaseView::POS_HEAD,
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'kartik\select2\Select2Asset',
    ];
}

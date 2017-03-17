<?php
namespace app\assets;

use app\classes\BaseView;
use yii\web\AssetBundle;

class LayoutMinimalAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web/views/layouts/';

    public $css = [
        'minimal.css',
    ];

    public $js = [
        'minimal.js',
    ];

    public $jsOptions = [
        'position' => BaseView::POS_HEAD,
    ];

    public $depends = [
        'app\assets\AppAsset',
    ];

}
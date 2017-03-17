<?php
namespace app\assets;

use app\classes\BaseView;
use yii\web\AssetBundle;

class LayoutMainAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web/views/layouts/';

    public $css = [
        'main.css',
    ];

    public $js = [
        'main.js',
    ];

    public $jsOptions = [
        'position' => BaseView::POS_HEAD,
    ];

    public $depends = [
        'app\assets\AppAsset',
    ];

}
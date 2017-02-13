<?php
namespace app\assets;

use yii\web\AssetBundle;

class BootstrapTableAsset extends AssetBundle
{
    public $sourcePath = '@vendor/wenzhixin/bootstrap-table/dist/';

    public $js = [
        'bootstrap-table.min.js',
        'locale/bootstrap-table-ru-RU.min.js',
    ];

    public $css = [
        ['bootstrap-table.min.css', 'media' => 'screen', 'type' => 'text/css'],
    ];
}
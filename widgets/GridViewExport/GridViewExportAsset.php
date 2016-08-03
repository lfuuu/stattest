<?php

namespace app\widgets\GridViewExport;

use yii\web\AssetBundle;

class GridViewExportAsset extends AssetBundle
{

    public
        $sourcePath = '@app/widgets/GridViewExport/assets/',
        $js = [
            'js/export.js',
        ],
            $css = [
            'css/export-columns.css',
            'css/export-data.css',
        ],
            $depends = [
            'yii\web\JqueryAsset',
        ];

}
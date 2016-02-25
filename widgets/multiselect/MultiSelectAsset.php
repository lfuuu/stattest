<?php

namespace app\widgets\multiselect;

use yii\web\AssetBundle;

class MultiSelectAsset extends AssetBundle
{

    public
        $sourcePath = '@bower/bower-asset/bootstrap-multiselect/dist',
        $js = [
            'js/bootstrap-multiselect.js',
            'js/bootstrap-multiselect-collapsible-groups.js',
        ],
        $css = [
            'css/bootstrap-multiselect.css'
        ],
        $depends = [
            'yii\bootstrap\BootstrapPluginAsset'
        ];

}
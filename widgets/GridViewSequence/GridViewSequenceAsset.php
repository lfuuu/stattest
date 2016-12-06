<?php

namespace app\widgets\GridViewSequence;

use yii\web\AssetBundle;

class GridViewSequenceAsset extends AssetBundle
{

    public
        $sourcePath = '@app/widgets/GridViewSequence/assets/',
        $js = [
            'js/sequence.js',
        ],
        $css = [
            'css/sequence.css',
        ],
        $depends = [
            'yii\web\JqueryAsset',
        ];

}
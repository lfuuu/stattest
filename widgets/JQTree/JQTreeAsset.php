<?php

namespace app\widgets\JQTree;

use yii\web\AssetBundle;

class JQTreeAsset extends AssetBundle
{

    public
        $sourcePath = '@app/widgets/JQTree/asset/',
        $js = [
            'js/tree.jquery.js',
        ],
        $css = [
            'css/jqtree.css',
            'css/jqtree-list.css',
            'css/jqtree-input.css',
        ],
        $depends = [
            'yii\web\JqueryAsset',
        ];

}
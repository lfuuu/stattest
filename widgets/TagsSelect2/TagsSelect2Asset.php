<?php

namespace app\widgets\TagsSelect2;

use yii\web\AssetBundle;

class TagsSelect2Asset extends AssetBundle
{

    public
        $sourcePath = '@app/widgets/TagsSelect2/assets/',
        $js = [
            'js/tags-select2.js',
        ],
        $depends = [
            'yii\web\JqueryAsset',
        ];

}
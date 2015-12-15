<?php

namespace app\widgets\select_multiply;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{

    public $sourcePath = '@app/web/multiple-select';

    //public $depends = ['yii\web\JqueryAsset'];

    public $js = ['multiple-select.js'];

    public $css = ['multiple-select.css'];

}
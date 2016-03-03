<?php

namespace app\widgets;

class MonthPickerAsset extends \yii\web\AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__ . '/../vendor/KidSysco/jquery-ui-month-picker'; // берем из другого виджета
        $this->js = [
            'MonthPicker' . (YII_DEBUG ? '' : '.min') . '.js',
        ];
        $this->css = [
            'css/MonthPicker' . (YII_DEBUG ? '' : '.min') . '.css',
        ];
        parent::init();
    }
}
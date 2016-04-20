<?php

namespace app\widgets;

class MonthPickerAsset extends \yii\web\AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@vendor/KidSysco/jquery-ui-month-picker/src'; // берем из другого виджета

        $this->js = [
            'MonthPicker' . (YII_DEBUG ? '' : '.min') . '.js',
        ];

        $this->css = [
            'MonthPicker' . (YII_DEBUG ? '' : '.min') . '.css',
        ];

        parent::init();
    }
}
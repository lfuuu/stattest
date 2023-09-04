<?php

namespace app\modules\nnp\column\traits;

use app\classes\Html;


trait ModelIsValid
{
    protected function renderIsValid($model, &$strValue)
    {
        if ($model->is_valid) {
            $html = Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']);
        } else {
            $html = Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']);
        }

        $strValue = $html . '&nbsp;' . $strValue;

        return $strValue;
    }
}
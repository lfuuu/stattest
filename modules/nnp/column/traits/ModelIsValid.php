<?php

namespace app\modules\nnp\column\traits;

use app\classes\Html;


trait ModelIsValid
{
    protected function renderSymbolIsValid($model, &$strValue)
    {
        if (is_bool($model->is_valid)) {
            $html = self::getSymbolHtml($model->is_valid);

            $strValue = $html . '&nbsp;' . $strValue;
        }

        return $strValue;
    }

    public static function getSymbolHtml(bool $isValid): string
    {
        return Html::tag('i', '', ['class' => 'glyphicon ' . ($isValid ? 'glyphicon-ok text-success' : 'glyphicon-remove text-danger')]);
    }
}
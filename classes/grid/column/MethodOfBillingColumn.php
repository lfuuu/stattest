<?php

namespace app\classes\grid\column;

use Yii;

class MethodOfBillingColumn extends DataColumn
{
    public $label = 'Метод тарификации';

    protected function renderDataCellContent($model, $key, $index)
    {
        $valueText = '';

        if ($model->tariffication_free_first_seconds > 0)
            $valueText = 'c 6 секунды, ';

        if ($model->tariffication_by_minutes > 0)
            $valueText .= 'поминутная';
        else {
            $valueText = 'посекундная';
            if ($model->tariffication_full_first_minute > 0)
                $valueText .= ' со второй минуты';
        }

        return $valueText;
    }
}
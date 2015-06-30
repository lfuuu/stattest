<?php

namespace app\classes\grid\column;

use Yii;
use yii\helpers\Html;
use app\helpers\FileHelper;

class SignatureColumn extends DataColumn
{
    public $attribute = 'signature_file_name';
    public $label = 'Подпись';

    protected function renderDataCellContent($model, $key, $index)
    {
        $value = parent::getDataCellValue($model, $key, $index);

        $file_exists = FileHelper::checkExists('SIGNATURE_DIR', $value);

        return Html::tag(
            'div',
            $file_exists ? 'Есть' : 'Нет',
            [
                'style' => 'text-align: center; color: ' . ($file_exists ? 'green' : 'red'),
            ]
        );
    }
}
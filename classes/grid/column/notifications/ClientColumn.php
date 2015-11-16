<?php

namespace app\classes\grid\column\notifications;

use Yii;
use yii\helpers\Html;
use app\classes\grid\column\DataColumn;

class ClientColumn extends DataColumn
{
    public $attribute = 'client_id';
    public $label = 'Л/С';

    protected function renderDataCellContent($model, $key, $index)
    {
        return Html::a(parent::getDataCellValue($model, $key, $index), ['client/view', 'id' => $model->id], ['target' => '_blank']);
    }

}
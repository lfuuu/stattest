<?php

namespace app\classes\grid\column\important_events;

use Yii;
use yii\helpers\Html;
use app\classes\grid\column\DataColumn;
use app\models\ClientAccount;

class ClientColumn extends DataColumn
{
    public
        $attribute = 'client_id',
        $label = 'Клиент';

    public function __construct($config = [])
    {
        $this->filterInputOptions['placeholder'] = 'ID клиента';
        parent::__construct($config);
    }

    protected function renderDataCellContent($model, $key, $index)
    {
        return
            $model->clientAccount !== null
            ? Html::a(
                $model->clientAccount->contragent->name,
                ['client/view', 'id' => $model->clientAccount->id],
                ['target' => '_blank']
              )
            : '';
    }

}
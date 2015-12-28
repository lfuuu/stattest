<?php

namespace app\classes\grid\column\important_events;

use Yii;
use yii\helpers\Html;
use app\classes\grid\column\DataColumn;
use app\models\ClientAccount;

class ClientColumn extends DataColumn
{
    public $attribute = 'client_id';
    public $label = 'Клиент';

    public function __construct($config = [])
    {
        $this->filterInputOptions['placeholder'] = 'ID клиента';
        parent::__construct($config);
    }

    protected function renderDataCellContent($model, $key, $index)
    {
        $value = parent::getDataCellValue($model, $key, $index);
        $clientAccount = ClientAccount::findOne($value);

        return
            $clientAccount instanceof ClientAccount
            ? Html::a(
                $clientAccount->contract->contragent->name,
                ['client/view', 'id' => $clientAccount->id],
                ['target' => '_blank']
              )
            : '';
    }

}
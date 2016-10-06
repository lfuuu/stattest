<?php

namespace app\classes\grid\column\important_events;

use Yii;
use yii\helpers\Html;
use app\classes\grid\column\DataColumn;
use app\classes\important_events\ImportantEventsDetailsFactory;

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
        $comment =
            !empty($model->comment)
                ? Html::tag('br') . Html::tag('label', $model->comment, ['class' => 'label label-default'])
                : '';

        $clientAccountName = ImportantEventsDetailsFactory::get($model->event, $model)->getProperty('client.name');

        return
            Html::a($clientAccountName, ['client/view', 'id' => $model->client_id], ['target' => '_blank']) .
            $comment;
    }

}
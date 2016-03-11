<?php

use kartik\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use app\classes\Html;
use app\classes\grid\GridView;
use app\models\important_events\ImportantEventsNames;
use app\forms\important_events\ImportantEventsNoticesForm;

echo Html::formLabel('Список подключенных событий');

echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Значимые события', 'url' => Url::toRoute(['/important_events/report'])],
        'Уведомления',
        'Список подключенных событий'
    ],
]);


/** @var ImportantEventsNames $dataProvider */
/** @var array $clientData */

$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
    'action' => Url::toRoute(['client/notices', 'clientAccountId' => $form->clientAccountId])
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'code',
            'label' => 'Название',
            'format' => 'raw',
            'value' => function($data) {
                return $data->value;
            },
            'width' => '*',
        ],
        [
            'label' => 'Email',
            'format' => 'raw',
            'value' => function($data) use ($clientData) {
                return Html::checkbox('FormData[events][' . $data->code . '][do_email]', isset($clientData[$data->code]) && $clientData[$data->code]['do_email']);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '8%',
        ],
        [
            'label' => 'Sms',
            'format' => 'raw',
            'value' => function($data) use ($clientData) {
                return Html::checkbox('FormData[events][' . $data->code . '][do_sms]', isset($clientData[$data->code]) && $clientData[$data->code]['do_sms']);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '8%',
        ],
        [
            'label' => 'ЛК',
            'format' => 'raw',
            'value' => function($data) use ($clientData) {
                return Html::checkbox('FormData[events][' . $data->code . '][do_lk]', isset($clientData[$data->code]) && $clientData[$data->code]['do_lk']);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '8%',
        ],
    ],
    'pjax' => false,
    'bordered' => true,
    'striped' => true,
    'condensed' => true,
    'hover' => true,
    'panel' => [
        'type' => GridView::TYPE_DEFAULT,
        'before' => Html::submitButton(
            '<i class="glyphicon glyphicon-plus"></i> Сохранить',
            [
                'class' => 'btn btn-primary btn-sm',
                'style' => 'float: right;',
            ]
        ),
        'after' => Html::submitButton(
            '<i class="glyphicon glyphicon-plus"></i> Сохранить',
            [
                'class' => 'btn btn-primary btn-sm',
                'style' => 'float: right;',
            ]
        ) . Html::tag('div', '', ['class' => 'clearfix']),
    ],
    'export' => false,
]);

ActiveForm::end();
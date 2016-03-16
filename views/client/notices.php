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

/** @var [] $dataProvider */

$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
    'action' => Url::toRoute(['client/notices', 'clientAccountId' => $form->clientAccountId])
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'event_name',
            'label' => 'Название',
            'width' => '*',
        ],
        [
            'attribute' => 'do_email',
            'label' => 'Email',
            'format' => 'raw',
            'value' => function($data) {
                return
                    Html::hiddenInput('FormData[events][' . $data['event_code'] . '][do_email]', 0) .
                    Html::checkbox('FormData[events][' . $data['event_code'] . '][do_email]', isset($data['do_email']) && $data['do_email']);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '8%',
        ],
        [
            'label' => 'Sms',
            'format' => 'raw',
            'value' => function($data) {
                return
                    Html::hiddenInput('FormData[events][' . $data['event_code'] . '][do_sms]', 0) .
                    Html::checkbox('FormData[events][' . $data['event_code'] . '][do_sms]', isset($data['do_sms']) && $data['do_sms']);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '8%',
        ],
        [
            'label' => 'ЛК',
            'format' => 'raw',
            'value' => function($data) {
                return
                    Html::hiddenInput('FormData[events][' . $data['event_code'] . '][do_lk]', 0) .
                    Html::checkbox('FormData[events][' . $data['event_code'] . '][do_lk]', isset($data['do_lk']) && $data['do_lk']);
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
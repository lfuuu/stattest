<?php

use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\models\document\PaymentTemplateType;
use yii\data\ActiveDataProvider;
use yii\widgets\Breadcrumbs;
use kartik\grid\ActionColumn;
use app\classes\grid\GridView;

/** @var ActiveDataProvider $dataProvider */
/** @var \app\classes\BaseView $baseView */

$baseView = $this;

echo Html::formLabel('Типы для документов');
echo Breadcrumbs::widget([
    'links' => [
        'Справочник',
        ['label' => $this->title = 'Типы для документов', 'url' => '/dictionary/payment-template-type'],
    ],
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'name',
            'format' => 'html',
            'value'     => function (PaymentTemplateType $model) {
                return Html::a($model->name, $model->getUrl());
            },
        ],
        [
            'attribute' => 'is_enabled',
            'value'     => function (PaymentTemplateType $model) {
                return $model->is_enabled ? 'Да' : 'Нет';
            },
        ],
        [
            'attribute' => 'data_source',
        ],
        [
            'attribute' => 'created_at',
            'value'     => function (PaymentTemplateType $model) {
                return DateTimeZoneHelper::getDateTime($model->created_at);
            },
        ],
        [
            'attribute' => 'updated_at',
            'value'     => function (PaymentTemplateType $model) {
                return DateTimeZoneHelper::getDateTime($model->updated_at);
            },
        ],
        [
            'class' => ActionColumn::class,
            'template' => '{update} {enable}',
            'buttons' => [
                'update' => function ($url, PaymentTemplateType $model) use ($baseView) {
                    return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]);
                },
                'enable' => function ($url, PaymentTemplateType $model) use ($baseView) {
                    if ($model->is_enabled) {
                        return $baseView->render('//layouts/_actionDisable', [
                            'url' => $model->getToggleEnableUrl(),
                        ]);
                    } else {
                        return $baseView->render('//layouts/_actionEnable', [
                            'url' => $model->getToggleEnableUrl(),
                        ]);
                    }
                },
            ],
            'hAlign' => GridView::ALIGN_CENTER,
        ],
    ],
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/dictionary/payment-template-type/edit']),
    'isFilterButton' => false,
    'floatHeader' => false,
]);

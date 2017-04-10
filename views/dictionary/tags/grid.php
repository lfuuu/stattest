<?php

use app\classes\BaseView;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use app\classes\grid\GridView;
use app\classes\Html;
use yii\widgets\Breadcrumbs;

/** @var BaseView $baseView */
/** @var $dataProvider ActiveDataProvider */
/** @var \app\forms\dictonary\tags\TagsForm $form */

$baseView = $this;

echo Html::formLabel('Метки');

echo Breadcrumbs::widget([
    'links' => [
        'Справочник',
        ['label' => 'Метки', 'url' => '/dictionary/tags'],
    ],
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'actions' => [
            'class' => 'kartik\grid\ActionColumn',
            'template' => Html::tag('div', '{update}', ['class' => 'text-center']),
            'buttons' => [
                'update' => function ($url, $model) use ($baseView) {
                    return $baseView->render('//layouts/_actionEdit', [
                        'url' => Url::toRoute([
                            '/dictionary/tags/edit',
                            'id' => $model->id,
                        ]),
                    ]);
                },
            ],
            'hAlign' => 'left',
        ],
        [
            'attribute' => 'name',
            'width' => '*',
        ],
        [
            'label' => 'Используется',
            'format' => 'raw',
            'value' => function ($model) use ($form) {
                return $form->resourcesMap($model->resourceNames);
            },
            'width' => '30%',
        ],
        [
            'attribute' => 'used_times',
            'width' => '10%',
        ],
    ],
    'floatHeader' => false,
    'isFilterButton' => false,
    'exportWidget' => false,
    'toggleData' => false,
]);
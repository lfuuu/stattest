<?php

use yii\widgets\Breadcrumbs;
use kartik\grid\ActionColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\CityBillingMethod;

echo Breadcrumbs::widget([
    'links' => [
        'Справочник',
        ['label' => $this->title = 'Методы биллингования', 'url' => '/dictionary/city-billing-methods/'],
    ],
]);

$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::className(),
        'template' => '{update} {delete}',
        'buttons' => [
            'update' => function ($url, CityBillingMethod $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, CityBillingMethod $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionDrop', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'width' => '100px',
        'hAlign' => GridView::ALIGN_CENTER,
    ],
    [
        'attribute' => 'id',
        'width' => '100px',
    ],
    [
        'attribute' => 'name',
        'width' => '*',
    ],
];

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/dictionary/city-billing-methods/new/']),
    'isFilterButton' => false,
    'columns' => $columns,
]);
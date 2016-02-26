<?php

use kartik\grid\GridView;
use app\classes\Html;

echo Html::formLabel('Услуга Звонок_чат');

echo GridView::widget([
    'dataProvider' => $dataProvider,
    //'filterModel' => $filterModel,
    'columns' => [
        [
            'class' => 'app\classes\grid\column\NameColumn',
            'attribute' => 'id',
            'label' => 'id',
            'vAlign' => 'top',
            'noWrap' => true,
        ],
        [
            'attribute' => 'client',
            'label' => 'Клиент'
        ],
        [
            'attribute' => 'actual_from'
        ],
        [
            'attribute' => 'actual_to'
        ],
        [
            'attribute' => 'tarif_id',
            'value' => function($model) {
                return $model->tariff->description;
            }
        ]

    ],
    'toolbar'=> [
        [
            'content' =>
                Html::a(
                    '<i class="glyphicon glyphicon-plus"></i> Добавить',
                    ['add'],
                    [
                        'data-pjax' => 0,
                        'class' => 'btn btn-success btn-sm form-lnk',
                    ]
                ),
        ]
    ],
    'bordered' => true,
    'striped' => true,
    'condensed' => true,
    'hover' => true,
    'panel'=>[
        'type' => GridView::TYPE_DEFAULT,
    ],
]);
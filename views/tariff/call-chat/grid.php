<?php

use kartik\grid\GridView;
use app\classes\Html;
use app\classes\enum\TariffStatusEnum;

echo Html::formLabel('Тарифы Звонок_чат');

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => [
        [
            'class' => 'app\classes\grid\column\NameColumn',
            'attribute' => 'description',
            'label' => 'Тариф',
            'vAlign' => 'top',
            'noWrap' => true,
        ],
        [
            'attribute' => 'price',
            'label' => 'Абонентская плата',
            'vAlign' => 'top',
        ],
        [
            'class' => 'app\classes\grid\column\CurrencyColumn',
            'attribute' => 'currency_id',
            'value' => 'currency_id',
            'vAlign' => 'top',
        ],
        [
            'class' => 'app\classes\grid\column\EnumColumn',
            'enum' => TariffStatusEnum::class,
            'label' => 'Статус тарифа',
            'attribute' => 'status'
        ],
        [
            'class' => 'app\classes\grid\column\BooleanColumn',
            'values' => [
                0 => 'Без НДС',
                1 => 'Вкл. НДС'
            ],
            'attribute' => 'price_include_vat',
            'label' => 'НДС',
            'vAlign' => 'top',
        ],
    ],
    'toolbar'=> [
        [
            'content' =>
                Html::a(
                    '<i class="glyphicon glyphicon-plus"></i> Добавить',
                    ['add'],
                    [
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
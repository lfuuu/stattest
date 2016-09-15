<?php

use app\classes\Html;
use kartik\grid\GridView;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\ConnectionPointColumn;
use app\classes\grid\column\CurrencyColumn;
use app\classes\grid\column\NameColumn;
use app\classes\grid\column\BooleanColumn;
use app\classes\grid\column\DestinationVoipColumn;

echo Html::formLabel('Тарифы IP Телефонии - Пакеты');

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => [
        [
            'attribute' => 'country_id',
            'label' => 'Страна',
            'class' => CountryColumn::className(),
            'vAlign' => 'top',
        ],
        [
            'class' => ConnectionPointColumn::className(),
            'vAlign' => 'top',
        ],
        [
            'class' => CurrencyColumn::className(),
            'vAlign' => 'top',
        ],
        [
            'class' => NameColumn::className(),
            'attribute' => 'name',
            'label' => 'Тариф',
            'vAlign' => 'top',
            'noWrap' => true,
        ],
        [
            'attribute' => 'periodical_fee',
            'label' => 'Абонентская плата',
            'vAlign' => 'top',
        ],
        [
            'attribute' => 'min_payment',
            'label' => 'Мин. платеж',
            'vAlign' => 'top',
        ],
        [
            'attribute' => 'minutes_count',
            'label' => 'Кол-во минут',
            'vAlign' => 'top',
        ],
        [
            'class' => BooleanColumn::className(),
            'values' => [0 => 'Без НДС', 1 => 'Вкл. НДС'],
            'attribute' => 'price_include_vat',
            'label' => 'НДС',
            'vAlign' => 'top',
        ],
        [
            'class' => DestinationVoipColumn::className(),
            'attribute' => 'destination_id',
            'vAlign' => 'top',
        ],
        [
            'attribute' => 'pricelist.name',
            'label' => 'Прайслист',
            'vAlign' => 'top',
        ],
    ],
    'pjax' => true,
    'toolbar' => [
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
    'panel' => [
        'type' => GridView::TYPE_DEFAULT,
    ],
]);
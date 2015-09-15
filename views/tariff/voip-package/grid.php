<?php

use kartik\grid\GridView;
use app\classes\Html;

echo Html::formLabel('Тарифы IP Телефонии - Пакеты');

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => [
        [
            'class' => 'app\classes\grid\column\CountryColumn',
            'vAlign' => 'top',
        ],
        [
            'class' => 'app\classes\grid\column\ConnectionPointColumn',
            'vAlign' => 'top',
        ],
        [
            'class' => 'app\classes\grid\column\CurrencyColumn',
            'vAlign' => 'top',
        ],
        [
            'class' => 'app\classes\grid\column\NameColumn',
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
            'class' => 'app\classes\grid\column\BooleanColumn',
            'values' => [0 => 'Без НДС', 1 => 'Вкл. НДС'],
            'attribute' => 'price_include_vat',
            'label' => 'НДС',
            'vAlign' => 'top',
        ],
        [
            'class' => 'app\classes\grid\column\DestinationVoipColumn',
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
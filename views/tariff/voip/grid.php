<?php

use app\classes\Html;
use app\modules\nnp\column\NdcTypeColumn;
use kartik\grid\GridView;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\ConnectionPointColumn;
use app\classes\grid\column\CurrencyColumn;
use app\classes\grid\column\NameColumn;
use app\classes\grid\column\VoipStatusesColumn;
use app\classes\grid\column\BooleanColumn;
use app\classes\grid\column\MethodOfBillingColumn;
use app\classes\grid\column\DestinationColumn;

echo Html::formLabel('Тарифы IP Телефонии');

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'beforeHeader' => [
        [
            'columns' => [
                ['content' => '', 'options' => ['colspan' => 10]],
                ['content' => 'Ежемесячно', 'options' => ['colspan' => 2]],
                ['content' => 'Подключение', 'options' => ['colspan' => 2]],
                ['content' => '', 'options' => ['colspan' => 3]],
            ],
        ]
    ],
    'columns' => [
        [
            'attribute' => 'country_id',
            'label' => 'Страна',
            'class' => CountryColumn::class,
            'vAlign' => 'top',
        ],
        [
            'class' => ConnectionPointColumn::class,
            'vAlign' => 'top',
        ],
        [
            'class' => CurrencyColumn::class,
            'vAlign' => 'top',
        ],
        [
            'class' => NameColumn::class,
            'attribute' => 'name',
            'label' => 'Тариф',
            'vAlign' => 'top',
            'noWrap' => true,
        ],
        [
            'class' => VoipStatusesColumn::class,
            'vAlign' => 'top',
        ],
        [
            'class' => NdcTypeColumn::class,
            'attribute' => 'ndc_type_id',
            'isWithNullAndNotNull' => false
        ],
        [
            'class' => BooleanColumn::class,
            'attribute' => 'is_default',
            'values' => [0 => 'Нет', 1 => 'Да'],
            'encodeLabel' => false,
            'label' => 'По-<wbr>умол&shy;чанию',
            'vAlign' => 'top',
        ],
        [
            'attribute' => 'free_local_min',
            'label' => 'Местных минут',
            'vAlign' => 'top',
        ],
        [
            'attribute' => 'month_min_payment',
            'label' => 'Мин. платеж',
            'vAlign' => 'top',
        ],
        [
            'class' => BooleanColumn::class,
            'attribute' => 'paid_redirect',
            'values' => [0 => 'нет', 1 => 'да'],
            'encodeLabel' => false,
            'label' => 'Платная пере&shy;адре&shy;са&shy;ция',
            'vAlign' => 'top',
        ],
        [
            'class' => MethodOfBillingColumn::class,
            'vAlign' => 'top',
        ],
        [
            'attribute' => 'month_line',
            'label' => 'за линию',
            'vAlign' => 'top',
        ],
        [
            'attribute' => 'month_number',
            'label' => 'за номер',
            'vAlign' => 'top',
        ],
        [
            'attribute' => 'once_line',
            'label' => 'за линию',
            'vAlign' => 'top',
        ],
        [
            'attribute' => 'once_number',
            'label' => 'за номер',
            'vAlign' => 'top',
        ],
        [
            'class' => BooleanColumn::class,
            'values' => [0 => 'Без НДС', 1 => 'Вкл. НДС'],
            'attribute' => 'price_include_vat',
            'label' => 'НДС',
            'vAlign' => 'top',
        ],
        [
            'class' => DestinationColumn::class,
            'attribute' => 'dest',
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
?>
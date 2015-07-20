<?php

use kartik\grid\GridView;
use app\classes\Html;
?>

<legend>
    Тарифы IP Телефонии
</legend>

<?php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'beforeHeader'=>[
        [
            'columns'=>[
                ['content' => '', 'options' => ['colspan' => 10]],
                ['content' => 'Ежемесячно', 'options' => ['colspan' => 2]],
                ['content' => 'Подключение', 'options' => ['colspan' => 2]],
                ['content' => '', 'options' => ['colspan' => 3]],
            ],
        ]
    ],
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
            'class' => 'app\classes\grid\column\VoipStatusesColumn',
            'vAlign' => 'top',
        ],
        [
            'class' => 'app\classes\grid\column\BooleanColumn',
            'attribute' => 'is_testing',
            'values' => [0 => '', 1 => 'по-умолчанию'],
            'label' => 'По-умолчанию',
            'noWrap' => true,
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
            'class' => 'app\classes\grid\column\BooleanColumn',
            'attribute' => 'paid_redirect',
            'values' => [0 => 'нет', 1 => 'да'],
            'label' => 'Платная переадресация',
            'vAlign' => 'top',
        ],
        [
            'class' => 'app\classes\grid\column\MethodOfBillingColumn',
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
            'class' => 'app\classes\grid\column\BooleanColumn',
            'values' => [0 => 'Без НДС', 1 => 'Вкл. НДС'],
            'attribute' => 'price_include_vat',
            'label' => 'НДС',
            'vAlign' => 'top',
        ],
        [
            'class' => 'app\classes\grid\column\DestinationColumn',
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
?>
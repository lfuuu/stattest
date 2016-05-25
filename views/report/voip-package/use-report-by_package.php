<?php

use yii\helpers\ArrayHelper;
use app\classes\grid\GridView;
use app\classes\DateTimeWithUserTimezone;
use app\classes\Html;

echo Html::label('Отчет по использованию пакетов на номере');

echo GridView::widget([
    'dataProvider' => $report,
    'beforeHeader' => [
        [
            'columns' => [
                ['content' => 'Номер', 'options' => ['rowspan' => 2],],
                ['content' => 'Название пакета', 'options' => ['rowspan' => 2],],
                ['content' => 'Абонентская плата', 'options' => ['rowspan' => 2],],
                ['content' => 'Минут', 'options' => ['colspan' => 2],],
                ['content' => 'Стоимость минуты в пакете', 'options' => ['rowspan' => 2],],
                ['content' => 'Минимальный платеж', 'options' => ['rowspan' => 2],],
                ['content' => 'Осталось неизрасходованно рублей в пакете', 'options' => ['rowspan' => 2, 'width' => '10%'],],
            ],
            'options' => [
                'class' => GridView::DEFAULT_HEADER_CLASS,
            ],
        ]
    ],
    'columns' => [
        [
            'headerOptions' => ['class' => 'hidden'],
            'format' => 'raw',
            'value' => function ($package) use ($filter) {
                return Html::a($package->usageVoip->E164, $package->usageVoip->helper->editLink, ['target' => '_blank']);
            },
        ],
        [
            'headerOptions' => ['class' => 'hidden'],
            'format' => 'raw',
            'value' => function ($package) {
                list($description) = $package->helper->description;
                return
                    $description .
                    Html::tag(
                        'i',
                        ' / ' .
                        (new DateTimeWithUserTimezone($package->actual_from))->formatWithInfinity('Y-m-d') .
                        ' : ' .
                        (new DateTimeWithUserTimezone($package->actual_to))->formatWithInfinity('Y-m-d')
                    );
            },
        ],
        [
            'headerOptions' => ['class' => 'hidden'],
            'format' => 'raw',
            'value' => function ($package) {
                return $package->tariff->periodical_fee;
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'label' => 'Всего',
            'format' => 'raw',
            'value' => function ($package) {
                return sprintf('%.2f', $package->tariff->minutes_count);
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'label' => 'Осталось',
            'format' => 'raw',
            'value' => function ($package) use ($filter) {
                /** @var app\models\UsageVoipPackage $package */
                /** @var app\classes\DynamicModel $filter */
                $stat = $package->getBillingStat($filter->date_range_from, $filter->date_range_to);
                return sprintf('%.2f', $package->tariff->minutes_count - array_sum(ArrayHelper::getColumn($stat, 'used_seconds')) / 60);
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'headerOptions' => ['class' => 'hidden'],
            'format' => 'raw',
            'value' => function ($package) {
                return ceil($package->tariff->periodical_fee / $package->tariff->minutes_count);
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'headerOptions' => ['class' => 'hidden'],
            'format' => 'raw',
            'value' => function ($package) {
                return $package->tariff->min_payment;
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'headerOptions' => ['class' => 'hidden'],
            'format' => 'raw',
            'value' => function ($package) use ($filter) {
                /** @var app\models\UsageVoipPackage $package */
                /** @var app\classes\DynamicModel $filter */
                if ($package->tariff->pricelist_id && $package->tariff->min_payment) {
                    $stat = $package->getCallsStat($filter->date_range_from, $filter->date_range_to);
                    $result = $package->tariff->min_payment - abs(array_sum(ArrayHelper::getColumn($stat, 'cost')));

                    return $result > 0 ? $result : 0;
                }
                return '--';
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
    ],
    'toolbar' => [],
    'panel' => [
        'type' => GridView::TYPE_DEFAULT,
    ],
]);
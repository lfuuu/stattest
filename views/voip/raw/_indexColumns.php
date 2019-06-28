<?php
/**
 * Колонки при стандартном выводе (без группировки) для отчета по calls_raw
 *
 * @var CallsRawFilter $filterModel
 */

use app\models\voip\filter\CallsRawFilter;
use app\classes\DateTimeWithUserTimezone;
use app\classes\grid\column\billing\DisconnectCauseColumn;

$columns = [
    [
        'label' => 'Время начала звонка',
        'attribute' => 'connect_time',
    ],
    [
        'label' => 'Длительность оригинации',
        'attribute' => 'session_time',
        'value' => function ($model) {
            return DateTimeWithUserTimezone::formatSecondsToDayAndHoursAndMinutesAndSeconds($model['session_time']);
        },
    ],
    [
        'label' => 'Длительность терминации',
        'attribute' => 'session_time_term',
        'value' => function ($model) {
            return DateTimeWithUserTimezone::formatSecondsToDayAndHoursAndMinutesAndSeconds($model['session_time_term']);
        },
    ],
    [
        'label' => 'Код завершения',
        'attribute' => 'disconnect_cause',
        'class' => DisconnectCauseColumn::class,
    ],
    [
        'label' => 'Номер А',
        'attribute' => 'src_number',
    ],
    [
        'label' => 'Оператор А',
        'attribute' => 'src_operator_name',
    ],
    [
        'label' => 'Страна А',
        'attribute' => 'src_country_name',
    ],
    [
        'label' => 'Регион А',
        'attribute' => 'src_region_name',
    ],
    [
        'label' => 'Город А',
        'attribute' => 'src_city_name',
    ],
    [
        'label' => 'Номер В',
        'attribute' => 'dst_number',
    ],
    [
        'label' => 'Оператор В',
        'attribute' => 'dst_operator_name',
    ],
    [
        'label' => 'Страна В',
        'attribute' => 'dst_country_name',
    ],
    [
        'label' => 'Регион В',
        'attribute' => 'dst_region_name',
    ],
    [
        'label' => 'Город В',
        'attribute' => 'dst_city_name',
    ],
    [
        'label' => 'Транк-оригинатор',
        'attribute' => 'src_route',
    ],
    [
        'label' => 'Договор A',
        'attribute' => 'src_contract_name',
    ],
    [
        'label' => 'Транк-терминатор',
        'attribute' => 'dst_route',
    ],
    [
        'label' => 'Договор B',
        'attribute' => 'dst_contract_name',
    ],
    [
        'label' => 'Продажа',
        'attribute' => 'sale',
        'value' => function ($model) use ($filterModel) {
            return $model['sale'] / $filterModel->currency_rate;
        },
        'format' => ['decimal', 4],
    ],
    [
        'label' => 'Себестоимость',
        'attribute' => 'cost_price',
        'value' => function ($model) use ($filterModel) {
            return $model['cost_price'] / $filterModel->currency_rate;
        },
        'format' => ['decimal', 4],
    ],
    [
        'label' => 'Маржа',
        'attribute' => 'margin',
        'value' => function ($model) use ($filterModel) {
            return $model['margin'] / $filterModel->currency_rate;
        },
        'format' => ['decimal', 4],
    ],
    [
        'label' => 'Стоимость минуты: оригинация',
        'attribute' => 'orig_rate',
        'value' => function ($model) use ($filterModel) {
            return $model['orig_rate'] / $filterModel->currency_rate;
        },
        'format' => ['decimal', 4],
    ],
    [
        'label' => 'Стоимость минуты: теминация',
        'attribute' => 'term_rate',
        'value' => function ($model) use ($filterModel) {
            return $model['term_rate'] / $filterModel->currency_rate;
        },
        'format' => ['decimal', 4],
    ],
    [
        'label' => 'ПДД',
        'attribute' => 'pdd',
    ],
];

if ($exceptColumns = $filterModel->getExceptColumns()) {
    foreach ($columns as &$column) {
        if (in_array($column['attribute'], $exceptColumns)) {
            $column = null;
        }
    }

    $columns = array_filter($columns);
}

return $columns;
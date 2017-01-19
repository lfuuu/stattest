<?php
/**
 * Колонки при стандартном выводе (без группировки) для отчета по calls_raw
 *
 * @var CallsRawFilter $filterModel
 */

use app\models\voip\filter\CallsRawFilter;
use app\classes\DateTimeWithUserTimezone;
use app\classes\grid\column\billing\DisconnectCauseColumn;

return [
    [
        'label' => 'Время начала звонка',
        'attribute' => 'connect_time',
    ],
    [
        'label' => 'Длительность разговора',
        'attribute' => 'session_time',
        'value' => function ($model) {
            return DateTimeWithUserTimezone::formatSecondsToMinutesAndSeconds($model['session_time']);
        },
    ],
    [
        'label' => 'Код завершения',
        'attribute' => 'disconnect_cause',
        'class' => DisconnectCauseColumn::className(),
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
        'label' => 'Регион А',
        'attribute' => 'src_region_name',
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
        'label' => 'Регион В',
        'attribute' => 'dst_region_name',
    ],
    [
        'label' => 'Транк-оригинатор',
        'attribute' => 'src_route',
    ],
    [
        'label' => 'Договор',
        'attribute' => 'src_contract_name',
    ],
    [
        'label' => 'Транк-терминатор',
        'attribute' => 'dst_route',
    ],
    [
        'label' => 'Договор',
        'attribute' => 'dst_contract_name',
    ],
    [
        'label' => 'Продажа',
        'attribute' => 'sale',
        'value' => function ($model) use ($filterModel) {
            return $model['sale'] / $filterModel->currency_rate;
        },
        'format' => ['decimal', 2],
    ],
    [
        'label' => 'Себестоимость',
        'attribute' => 'cost_price',
        'value' => function ($model) use ($filterModel) {
            return $model['cost_price'] / $filterModel->currency_rate;
        },
        'format' => ['decimal', 2],
    ],
    [
        'label' => 'Маржа',
        'attribute' => 'margin',
        'value' => function ($model) use ($filterModel) {
            return $model['margin'] / $filterModel->currency_rate;
        },
        'format' => ['decimal', 2],
    ],
    [
        'label' => 'Стоимость минуты: оригинация',
        'attribute' => 'orig_rate',
        'value' => function ($model) use ($filterModel) {
            return $model['orig_rate'] / $filterModel->currency_rate;
        },
        'format' => ['decimal', 2],
    ],
    [
        'label' => 'Стоимость минуты: теминация',
        'attribute' => 'term_rate',
        'value' => function ($model) use ($filterModel) {
            return $model['term_rate'] / $filterModel->currency_rate;
        },
        'format' => ['decimal', 2],
    ],
    [
        'label' => 'ПДД',
        'attribute' => 'pdd',
    ],
];
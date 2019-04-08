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
        'class' => DisconnectCauseColumn::class,
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
        'value' => function ($model) {
            return $model['sale'] / $this->currency_rate;
        },
        'format' => ['decimal', 4],
    ],
    [
        'label' => 'Себестоимость',
        'attribute' => 'cost_price',
        'value' => function ($model) {
            return $model['cost_price'] / $this->currency_rate;
        },
        'format' => ['decimal', 4],
    ],
    [
        'label' => 'Маржа',
        'attribute' => 'margin',
        'value' => function ($model) {
            return $model['margin'] / $this->currency_rate;
        },
        'format' => ['decimal', 4],
    ],
    [
        'label' => 'Стоимость минуты: оригинация',
        'attribute' => 'orig_rate',
        'value' => function ($model) {
            return $model['orig_rate'] / $this->currency_rate;
        },
        'format' => ['decimal', 4],
    ],
    [
        'label' => 'Стоимость минуты: теминация',
        'attribute' => 'term_rate',
        'value' => function ($model) {
            return $model['term_rate'] / $this->currency_rate;
        },
        'format' => ['decimal', 4],
    ],
];
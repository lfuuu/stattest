<?php

use app\classes\DateTimeWithUserTimezone;
use app\classes\grid\GridView;
use app\classes\Html;
use app\helpers\DateTimeZoneHelper;

echo Html::label('Отчет по звонкам в пакете на номере');

echo GridView::widget([
    'dataProvider' => $report,
    'columns' => [
        [
            'label' => 'ID',
            'format' => 'raw',
            'value' => function ($data) {
                return $data['id'];
            },
        ],
        [
            'label' => 'Дата / Время',
            'format' => 'raw',
            'value' => function ($data) {
                $datetime = $data['tsf1'];

                if (!is_null($data['ts1'])) {
                    $datetime =
                        (
                        new DateTimeWithUserTimezone(
                            $data['tsf1'],
                            new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)
                        )
                        )
                            ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW))
                            ->format(DateTimeZoneHelper::DATETIME_FORMAT);
                }

                return $datetime;
            },
        ],
        [
            'label' => 'Исходящий номер',
            'format' => 'raw',
            'value' => function ($data) {
                return (isset($data['src_number']) ? $data['src_number'] : '');
            },
        ],
        [
            'label' => 'Направление',
            'format' => 'raw',
            'value' => function ($data) {
                if (isset($data['orig'])) {
                    switch ($data['orig']) {
                        case true:
                            return '&uarr;&nbsp;исходящий';
                        case false:
                            return '&darr;&nbsp;входящий';
                    }
                }
                return '';
            },
        ],
        [
            'label' => 'Входящий номер',
            'format' => 'raw',
            'value' => function ($data) {
                return (isset($data['dst_number']) ? $data['dst_number'] : '');
            },
        ],
        [
            'label' => 'Время разговора',
            'format' => 'raw',
            'value' => function ($data) {
                return $data['tsf2'];
            },
        ],
        [
            'label' => 'Стоимость',
            'format' => 'raw',
            'value' => function ($data) {
                return $data['price'];
            },
        ],
        [
            'label' => 'Назначение',
            'format' => 'raw',
            'value' => function ($data) {
                return $data['geo'];
            },
        ],
    ],
    'pjax' => false,
    'toolbar' => [],
    'bordered' => true,
    'striped' => true,
    'condensed' => true,
    'hover' => true,
    'panel' => [
        'type' => GridView::TYPE_DEFAULT,
    ],
    'panelHeadingTemplate' => '
        <div class="pull-right">
            {extraButtons}
        </div>
        <div class="pull-left">
            {summary}
        </div>
        <h3 class="panel-title">
            {heading}
        </h3>
        <div class="clearfix"></div>
    ',
]);
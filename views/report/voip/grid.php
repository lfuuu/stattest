<?php

use kartik\grid\GridView;
use app\classes\DateFunction;
use app\classes\Html;
use app\forms\user\GroupForm;
use app\widgets\DateControl;
use app\classes\report\LostCalls;

$regions = \app\models\Region::getList();

/** @var GroupForm $dataProvider */

echo Html::formLabel('Потерянные номера на ' . DateFunction::mdate(strtotime($date), 'd месяца Yг' . ($region ? ' по городу ' . $regions[$region] : '')));

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'label' => 'ID',
            'attribute' => 'id',
        ],
        [
            'label' => 'Cdr ID',
            'attribute' => 'cdr_id',
        ],
        [
            'label' => 'Время звонка',
            'value' => function ($data) {
                return explode(' ', $data['connect_time'])[1];
            },
            'attribute' => 'connect_time',
        ],
        [
            'label' => 'Номер А',
            'attribute' => 'src_number',
        ],
        [
            'label' => 'Номер Б',
            'attribute' => 'dst_number',
        ],
        [
            'label' => 'Продолжительность звонка',
            'value' => function ($data) {
                return date('H:i:s', $data['billed_time']);
            }
        ],
    ],
    'pjax' => true,
    'toolbar' => [
        [
            'content' =>
                '
                <form class="form-inline" method="get">
                  <div class="form-group">
                    <label class="sr-only" for="serachMode">Режим</label>'
                . Html::dropDownList('mode', $mode, LostCalls::$modes, [
                    'class' => 'form-control',
                    'id' => 'serachMode',
                ])
                . '</div>
                  <div class="form-group">
                    <label class="sr-only" for="searchByDate">Дата</label>'
                . DateControl::widget(
                    [
                        'name' => 'date',
                        'value' => $date,
                        'autoWidgetSettings' => [
                            DateControl::FORMAT_DATE => [
                                'class' => '\app\widgets\DatePicker',
                                'type' => \app\widgets\DatePicker::TYPE_COMPONENT_PREPEND,
                                'options' => [
                                    'addons' => [
                                        'todayButton' => [],
                                    ],
                                ],
                            ],
                        ],
                    ]
                )
                . '</div>
                  <div class="form-group">
                    <label class="sr-only" for="serachByRegion">Регион</label>'
                . Html::dropDownList('region', $region, $regions, [
                    'class' => 'form-control',
                    'id' => 'serachByRegion',
                ])
                . '</div>
                 <div class="form-group">'
                . Html::submitButton(
                    '<i class="glyphicon glyphicon-search"></i> Искать',
                    ['class' => 'btn btn-success btn-sm form-lnk']
                )
                . '</div></form>'
            ,
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
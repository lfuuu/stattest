<?php
/**
 * Main page view for Raw report (/voip/raw)
 *
 * @var CallsRawFilter $filterModel
 * @var \yii\web\View $this
 */

use app\classes\grid\GridView;
use app\models\voip\filter\CallsRawFilter;
use yii\widgets\Breadcrumbs;
use app\classes\grid\column\universal\DateTimeRangeDoubleColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\CheckboxColumn;
use app\classes\grid\column\billing\ServiceTrunkColumn;
use app\classes\grid\column\billing\ServerColumn;
use app\classes\grid\column\billing\ContractColumn;
use app\classes\grid\column\universal\NnpOperatorColumn;
use app\classes\grid\column\universal\NnpRegionColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\billing\DisconnectCauseColumn;
use app\classes\grid\column\universal\ConstructColumn;
use app\classes\grid\column\universal\WithEmptyFilterColumn;
use app\classes\grid\column\universal\CountryColumn;

?>

<?= app\classes\Html::formLabel($this->title = 'Отчет по данным calls_raw') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Телефония'],
        ['label' => $this->title],
    ],
]);

$aggrDigitCount = [
    'sale_sum' => 2,
    'sale_avg' => 2,
    'sale_min' => 2,
    'sale_max' => 2,
    'cost_price_sum' => 2,
    'cost_price_avg' => 2,
    'cost_price_min' => 2,
    'cost_price_max' => 2,
    'margin_sum' => 2,
    'margin_avg' => 2,
    'margin_min' => 2,
    'margin_max' => 2,
    'session_time_avg' => 2,
];

$filter = [
    [
        'attribute' => 'server_ids',
        'label' => 'Точка присоединения',
        'isWithEmpty' => false,
        'class' => ServerColumn::className(),
        'value' => $filterModel->server_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'src_routes_ids',
        'label' => 'Транк-оригинатор',
        'class' => ServiceTrunkColumn::className(),
        'filterByServerId' => $filterModel->server_ids,
        'filterByServiceTrunkId' => $filterModel->src_contracts_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ]
    ],
    [
        'attribute' => 'src_number',
        'label' => 'Маска номера А',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'dst_number',
        'label' => 'Маска номера В',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'connect_time',
        'label' => 'Время начала',
        'class' => DateTimeRangeDoubleColumn::className(),
        'filterOptions' => [
            'class' => 'alert-danger'
        ],
    ],
    [
        'attribute' => 'src_contracts_ids',
        'label' => 'Договор номера А',
        'class' => ContractColumn::className(),
        'filterByServiceTrunkId' => $filterModel->src_routes_ids,
        'filterByServerId' => $filterModel->server_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'src_operator_ids',
        'label' => 'Оператор номера А',
        'class' => NnpOperatorColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'dst_operator_ids',
        'label' => 'Оператор номера В',
        'class' => NnpOperatorColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'session_time',
        'label' => 'Длительность разговора',
        'class' => IntegerRangeColumn::className(),
        'options' => [
            'min' => 0,
        ],
    ],
    [
        'attribute' => 'dst_routes_ids',
        'label' => 'Транк-терминатор',
        'class' => ServiceTrunkColumn::className(),
        'filterByServiceTrunkId' => $filterModel->dst_contracts_ids,
        'filterByServerId' => $filterModel->server_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'src_region_ids',
        'label' => 'Регион номера А',
        'class' => NnpRegionColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'dst_region_ids',
        'label' => 'Регион номера B',
        'class' => NnpRegionColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'disconnect_causes',
        'label' => 'Код завершения',
        'class' => DisconnectCauseColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'dst_contracts_ids',
        'label' => 'Договор номера B',
        'class' => ContractColumn::className(),
        'filterByServiceTrunkId' => $filterModel->dst_routes_ids,
        'filterByServerId' => $filterModel->server_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'src_country_prefixes',
        'label' => 'Страна номера А',
        'class' => CountryColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
        'indexBy' => 'prefix',
    ],
    [
        'attribute' => 'dst_country_prefixes',
        'label' => 'Страна номера B',
        'class' => CountryColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'is_success_calls',
        'label' => 'Только успешные попытки',
        'class' => CheckboxColumn::className(),
    ],
    [
        'class' => WithEmptyFilterColumn::className(),
    ],
    [
        'class' => WithEmptyFilterColumn::className(),
    ],
    [
        'class' => WithEmptyFilterColumn::className(),
    ],
    [
        'attribute' => 'group_period',
        'label' => 'Период группировки',
        'class' => ConstructColumn::className(),
        'filterOptions' => [
            'class' => ' col-sm-4'
        ],
        'filter' => [
            '' => 'За весь период',
            'month' => 'По месяцам',
            'day' => 'По дням',
            'hour' => 'По часам'
        ],
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'group',
        'label' => 'Группировки',
        'class' => ConstructColumn::className(),
        'filterOptions' => [
            'class' => ' col-sm-4'
        ],
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'filter' => $filterModel->groupConst,
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'aggr',
        'label' => 'Что считать',
        'class' => ConstructColumn::className(),
        'filterOptions' => [
            'class' => ' col-sm-4'
        ],
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'filter' => $filterModel->aggrLabels,
        'isWithEmpty' => false,
    ],
];

$columns = [];
if ($filterModel->group || $filterModel->group_period || $filterModel->aggr) {
    if ($filterModel->group_period) {
        $columns[] = [
            'label' => 'Интервал',
            'attribute' => 'interval',
        ];
    }

    if ($filterModel->group) {
        foreach ($filterModel->group as $value) {
            $columns[] = [
                'label' => $filterModel->groupConst[$value],
                'attribute' => $value
            ];
        }
    }

    $c = count($columns);
    foreach ($filterModel->aggr as $key => $value) {
        $columns[$key + $c] = [
            'label' => $filterModel->aggrLabels[$value],
            'attribute' => $value,
        ];
        if (isset($aggrDigitCount[$value])) {
            $columns[$key + $c]['format'] = ['decimal', $aggrDigitCount[$value]];
        }
    }
} else {
    $columns = [
        [
            'label' => 'Время начала звонка',
            'attribute' => 'connect_time',
        ],
        [
            'label' => 'Длительность разговора',
            'attribute' => 'session_time',
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
            'format' => ['decimal', 2],
        ],
        [
            'label' => 'Себестоимость',
            'attribute' => 'cost_price',
            'format' => ['decimal', 2],
        ],
        [
            'label' => 'Маржа',
            'attribute' => 'margin',
            'format' => ['decimal', 2],
        ],
        [
            'label' => 'Стоимость минуты: оригинация',
            'attribute' => 'orig_rate',
        ],
        [
            'label' => 'Стоимость минуты: теминация',
            'attribute' => 'term_rate',
        ],
        [
            'label' => 'ПДД',
            'attribute' => 'pdd',
        ],
    ];
}

try {
    GridView::separateWidget(
        [
            'dataProvider' => $filterModel->getReport(),
            'filterModel' => $filterModel,
            'beforeHeader' => [
                'columns' => $filter
            ],
            'columns' => $columns,
            'pjaxSettings' => [
                'formSelector' => false,
                'linkSelector' => false,
                'enableReplaceState' => true,
                'timeout' => 180000,
            ],
            'filterPosition' => '',
            'emptyText' => isset($emptyText) ? $emptyText : ($filterModel->isFilteringPossible() ?
                Yii::t('yii', 'No results found.') :
                'Выберите время начала разговора и хотя бы еще одно поле'),
        ]
    );
} catch (yii\db\Exception $e) {
    if ($e->getCode() == 8) {
        Yii::$app->session->addFlash(
            'error',
            'Запрос слишком тяжелый, чтобы выполниться. Задайте, пожалуйста, другие фильтры'
        );
    } else {
        Yii::$app->session->addFlash('error', "Ошибка выполнения запроса: " . $e->getMessage());
    }
}

?>

<script type='text/javascript'>
    $(function () {
        var settings = {"theme":"krajee","width":"100%","language":"ru-RU"};

        $('select[name="CallsRawFilter[server_ids][]"], select[name="CallsRawFilter[src_routes_ids][]"], select[name="CallsRawFilter[src_contracts_ids][]"]')
            .on('change', function () {
                var server_ids = $('*[name="CallsRawFilter[server_ids][]"]'),
                    src_contracts_ids = $('*[name="CallsRawFilter[src_contracts_ids][]"]'),
                    src_routes_ids = $('*[name="CallsRawFilter[src_routes_ids][]"]');

                if (!$(this).is(src_routes_ids))
                    $.get("/voip/raw/get-routes", {
                            serverIds: server_ids.val(),
                            serviceTrunkId: src_contracts_ids.val()
                        }, function (data) {
                            src_routes_ids.html(data).select2(settings);
                        });
                if (!$(this).is(src_contracts_ids))
                    $.get("/voip/raw/get-contracts", {
                            serverIds: server_ids.val(),
                            serviceTrunkId: src_routes_ids.val()
                        }, function (data) {
                            src_contracts_ids.html(data).select2(settings);
                        });
        });

        $('select[name="CallsRawFilter[server_ids][]"], select[name="CallsRawFilter[dst_routes_ids][]"], select[name="CallsRawFilter[dst_contracts_ids][]"]')
            .on('change', function () {
                var server_ids = $('*[name="CallsRawFilter[server_ids][]"]'),
                    dst_contracts_ids = $('*[name="CallsRawFilter[dst_contracts_ids][]"]'),
                    dst_routes_ids = $('*[name="CallsRawFilter[dst_routes_ids][]"]');

                if (!$(this).is(dst_routes_ids))
                    $.get("/voip/raw/get-routes", {
                            serverIds: server_ids.val(),
                            serviceTrunkId: dst_contracts_ids.val()
                        }, function (data) {
                            dst_routes_ids.html(data).select2(settings);
                        });
                if (!$(this).is(dst_contracts_ids))
                    $.get("/voip/raw/get-contracts", {
                            serverIds: server_ids.val(),
                            serviceTrunkId: dst_routes_ids.val()
                        }, function (data) {
                            dst_contracts_ids.html(data).select2(settings);
                        });
        });

    });
</script>

<?php
/**
 * Main page view for CDR report (/voip/cdr)
 *
 * @var Cdr $filterModel
 * @var \yii\web\View $this
 */

use app\classes\grid\GridView;
use app\models\voip\filter\Cdr;
use yii\widgets\Breadcrumbs;
use app\classes\grid\column\universal\DateTimeRangeDoubleColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\CheckboxColumn;
use app\classes\grid\column\billing\ServiceTrunkColumn;
use app\classes\grid\column\billing\ServerColumn;
use app\classes\grid\column\billing\ContractColumn;
use app\classes\grid\column\billing\ReleasingPartyColumn;
use app\classes\grid\column\universal\NnpOperatorColumn;
use app\classes\grid\column\universal\NnpRegionColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\billing\DisconnectCauseColumn;
use app\classes\grid\column\universal\ConstructColumn;
use app\classes\grid\column\universal\WithEmptyFilterColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\modules\nnp\column\DestinationColumn;

?>

<?= app\classes\Html::formLabel($this->title = 'Отчет по данным calls_cdr') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Телефония'],
        ['label' => $this->title],
    ],
]);

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
        'filterOptions' => [
            'class' => 'alert-danger'
        ],
    ],
    [
        'attribute' => 'src_routes',
        'label' => 'Транк-оригинатор',
        'class' => ServiceTrunkColumn::className(),
        'filterByServerId' => $filterModel->server_ids,
        'filterByContractId' => $filterModel->src_contracts,
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
        'attribute' => 'src_contracts',
        'label' => 'Договор номера А',
        'class' => ContractColumn::className(),
        'filterByTrunkName' => $filterModel->src_routes,
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
    ],
    [
        'attribute' => 'dst_routes',
        'label' => 'Транк-терминатор',
        'class' => ServiceTrunkColumn::className(),
        'filterByContractId' => $filterModel->dst_contracts,
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
        'attribute' => 'call_id',
        'label' => 'call_id',
        'class' => IntegerColumn::className(),
    ],
    [
        'attribute' => 'dst_contracts',
        'label' => 'Договор номера B',
        'class' => ContractColumn::className(),
        'filterByTrunkName' => $filterModel->dst_routes,
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
        'attribute' => 'disconnect_causes',
        'label' => 'Код завершения',
        'class' => DisconnectCauseColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'releasing_party',
        'label' => 'Инициатор завершения',
        'class' => ReleasingPartyColumn::className(),
    ],
    [
        'attribute' => 'src_destination_ids',
        'label' => 'Направление номера А',
        'class' => DestinationColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'dst_destination_ids',
        'label' => 'Направление номера А',
        'class' => DestinationColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'redirect_number',
        'label' => 'Redirect number',
        'class' => StringColumn::className(),
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
    if ($filterModel->group) {
        foreach ($filterModel->group as $value) {
            $columns[] = [
                'label' => $filterModel->groupConst[$value],
                'attribute' => $value
            ];
        }
    }

    if ($filterModel->group_period) {
        $columns[] = [
            'label' => 'Интервал',
            'attribute' => 'interval',
        ];
    }

    foreach ($filterModel->aggr as $value) {
        $columns[] = [
            'label' => $filterModel->aggrLabels[$value],
            'attribute' => $value
        ];
    }
} else {
    $columns = [
        [
            'label' => 'Идентификатор звонка',
            'attribute' => 'call_id',
        ],
        [
            'label' => 'Время начала',
            'attribute' => 'setup_time',
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
            'label' => 'Redirect number',
            'attribute' => 'redirect_number',
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
        ],
        [
            'label' => 'Себестоимость',
            'attribute' => 'cost_price',
        ],
        [
            'label' => 'Маржа',
            'attribute' => 'margin',
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
            'label' => 'Инициатор завершения',
            'attribute' => 'releasing_party',
        ],
        [
            'label' => 'Время соединения',
            'attribute' => 'connect_time',
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
            'panelHeadingTemplate' => <<< HTML
        <div class="pull-right">
            {extraButtons}
            {filterButton}
            {floatThead}
            {toggleData}
            {export}
        </div>
        <h3 class="panel-title">
            {heading}
        </h3>
        <div class="clearfix"></div>
HTML
            ,
            'emptyText' => isset($emptyText) ? $emptyText : ($filterModel->isFilteringPossible() ?
                Yii::t('yii', 'No results found.') :
                'Выберите точку присоединения и время начала разговора'),
        ]
    );
} catch (yii\db\Exception $e) {
    if ($e->getCode() == 8) {
        Yii::$app->session->addFlash(
            'error',
            'Запрос слишком тяжелый чтобы выполниться. Задайте, пожалуйста, другие фильтры'
        );
    } else {
        Yii::$app->session->addFlash('error', "Ошибка выполнения запроса: " . $e->getMessage());
    }
}

?>

<script type='text/javascript'>
    $(function () {
        $('select[name="Cdr[server_ids][]"], select[name="Cdr[src_routes][]"], select[name="Cdr[src_contracts][]"]')
            .on('change', function () {
                var server_id = $('*[name="Cdr[server_ids][]"]'),
                    src_contract = $('*[name="Cdr[src_contracts][]"]'),
                    src_route = $('*[name="Cdr[src_routes][]"]');

                if (!$(this).is(src_route))
                    $.get("/voip/cdr/get-routes", {
                            server_id: server_id.val(),
                            contract: src_contract.val()
                        }, function (data) {
                            src_route.html(data).select2({"theme":"krajee","width":"100%","language":"ru-RU"});
                        });
                if (!$(this).is(src_contract))
                    $.get("/voip/cdr/get-contracts", {
                            server_id: server_id.val(),
                            trunk: src_route.val()
                        }, function (data) {
                            src_contract.html(data).select2({"theme":"krajee","width":"100%","language":"ru-RU"});
                        });
        });

        $('select[name="Cdr[server_ids][]"], select[name="Cdr[dst_routes][]"], select[name="Cdr[dst_contracts][]"]')
            .on('change', function () {
                var server_id = $('*[name="Cdr[server_ids][]"]'),
                    dst_contract = $('*[name="Cdr[dst_contracts][]"]'),
                    dst_route = $('*[name="Cdr[dst_routes][]"]');

                if (!$(this).is(dst_route))
                    $.get("/voip/cdr/get-routes", {
                            server_id: server_id.val(),
                            contract: dst_contract.val()
                        }, function (data) {
                            dst_route.html(data).select2({"theme":"krajee","width":"100%","language":"ru-RU"});
                        });
                if (!$(this).is(dst_contract))
                    $.get("/voip/cdr/get-contracts", {
                            server_id: server_id.val(),
                            trunk: dst_route.val()
                        }, function (data) {
                            dst_contract.html(data).select2({"theme":"krajee","width":"100%","language":"ru-RU"});
                        });
        });

    });
</script>

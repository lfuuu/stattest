<?php
/**
 * Фильтры для отчета по calls_raw
 *
 * @var CallsRawFilter $filterModel
 */

use app\models\voip\filter\CallsRawFilter;
use app\classes\grid\column\universal\DateTimeRangeDoubleColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\CheckboxColumn;
use app\classes\grid\column\billing\ServiceTrunkColumn;
use app\classes\grid\column\billing\ServerColumn;
use app\classes\grid\column\billing\ContractColumn;
use app\classes\grid\column\universal\NnpOperatorColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\billing\DisconnectCauseColumn;
use app\classes\grid\column\universal\ConstructColumn;
use app\classes\grid\column\universal\WithEmptyFilterColumn;
use app\modules\nnp\column\CountryColumn;
use app\classes\grid\column\universal\CurrencyColumn;
use app\modules\nnp\column\CityColumn;
use app\modules\nnp\column\RegionColumn;

return [
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
        'attribute' => 'src_contries_ids',
        'label' => 'Страна номера А',
        'class' => CountryColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'dst_contries_ids',
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
        'attribute' => 'src_regions_ids',
        'label' => 'Регион номера А',
        'class' => RegionColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'countryCodes' => $filterModel->src_contries_ids,
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => false,
    ],
    [
        'attribute' => 'dst_regions_ids',
        'label' => 'Регион номера B',
        'class' => RegionColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'countryCodes' => $filterModel->dst_contries_ids,
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => false,
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
        'attribute' => 'src_cities_ids',
        'label' => 'Город номера А',
        'class' => CityColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'countryCodes' => $filterModel->src_contries_ids,
        'regionIds' => $filterModel->src_regions_ids,
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'dst_cities_ids',
        'label' => 'Город номера B',
        'class' => CityColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'countryCodes' => $filterModel->dst_contries_ids,
        'regionIds' => $filterModel->dst_regions_ids,
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'currency',
        'label' => 'Валюта расчетов',
        'class' => CurrencyColumn::className(),
        'isWithEmpty' => false,
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
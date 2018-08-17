<?php
/**
 * Фильтры для отчета по calls_raw
 *
 * @var CallsRawFilter $filterModel
 * @var bool $isSupport
 * @var bool $isCache
 */

use app\classes\grid\column\billing\ContractColumn;
use app\classes\grid\column\billing\DisconnectCauseColumn;
use app\classes\grid\column\billing\ServerColumn;
use app\classes\grid\column\billing\ServiceTrunkColumn;
use app\classes\grid\column\billing\TrunkColumn;
use app\classes\grid\column\universal\CheckboxColumn;
use app\classes\grid\column\universal\ConstructColumn;
use app\classes\grid\column\universal\CurrencyColumn;
use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\DateTimeRangeDoubleColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\WithEmptyFilterColumn;
use app\models\voip\filter\CallsRawFilter;
use app\modules\nnp\column\CityColumn;
use app\modules\nnp\column\CountryColumn;
use app\modules\nnp\column\DestinationColumn;
use app\modules\nnp\column\NdcTypeColumn;
use app\modules\nnp\column\OperatorColumn;
use app\modules\nnp\column\RegionColumn;
use app\classes\grid\column\billing\TrunkGroupColumn;

return [
    [
        'attribute' => 'connect_time',
        'class' => $isSupport && $isCache ?
            DateRangeDoubleColumn::className() : DateTimeRangeDoubleColumn::className(),
        'filterOptions' => [
            'class' => 'alert-danger'
        ],
    ],
    [
        'class' => WithEmptyFilterColumn::className(),
        'filterOptions' => [
            'class' => 'no_display'
        ],
    ],
    [
        'class' => WithEmptyFilterColumn::className(),
        'filterOptions' => [
            'class' => 'no_display'
        ],
    ],
    [
        'attribute' => 'server_ids',
        'isWithEmpty' => false,
        'class' => ServerColumn::className(),
        'value' => $filterModel->server_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'src_number',
        'class' => StringColumn::className(),
        'filterOptions' => [
            'title' => 'Допустимы цифры, _ или . (одна любая цифра), % или * (любая последовательность цифр, в том числе пустая строка)'
        ],
    ],
    [
        'attribute' => 'dst_number',
        'class' => StringColumn::className(),
        'filterOptions' => [
            'title' => 'Допустимы цифры, _ или . (одна любая цифра), % или * (любая последовательность цифр, в том числе пустая строка)'
        ],
    ],
    [
        'attribute' => 'src_trunk_group_ids',
        'class' => TrunkGroupColumn::className(),
        'filterByServerIds' => $filterModel->server_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false
    ],
    [
        'attribute' => 'dst_trunk_group_ids',
        'class' => TrunkGroupColumn::className(),
        'filterByServerIds' => $filterModel->server_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false
    ],
    [
        'attribute' => 'src_physical_trunks_ids',
        'class' => TrunkColumn::className(),
        'filterByServerIds' => $filterModel->server_ids,
        'filterByTrunkGroupIds' => $filterModel->src_trunk_group_ids,
        'filterByServiceTrunkIds' => $filterModel->src_logical_trunks_ids,
        'filterByContractIds' => $filterModel->src_contracts_ids,
        'filterByShowInStat' => false,
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false
    ],
    [
        'attribute' => 'dst_physical_trunks_ids',
        'class' => TrunkColumn::className(),
        'filterByServerIds' => $filterModel->server_ids,
        'filterByTrunkGroupIds' => $filterModel->dst_trunk_group_ids,
        'filterByServiceTrunkIds' => $filterModel->dst_logical_trunks_ids,
        'filterByContractIds' => $filterModel->dst_contracts_ids,
        'filterByShowInStat' => false,
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false
    ],
    [
        'attribute' => 'src_operator_ids',
        'class' => OperatorColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'dst_operator_ids',
        'class' => OperatorColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'src_logical_trunks_ids',
        'class' => ServiceTrunkColumn::className(),
        'filterByServerIds' => $filterModel->server_ids,
        'filterByContractIds' => $filterModel->src_contracts_ids,
        'filterByTrunkIds' => $filterModel->src_physical_trunks_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ]
    ],
    [
        'attribute' => 'dst_logical_trunks_ids',
        'class' => ServiceTrunkColumn::className(),
        'filterByContractIds' => $filterModel->dst_contracts_ids,
        'filterByServerIds' => $filterModel->server_ids,
        'filterByTrunkIds' => $filterModel->dst_physical_trunks_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'src_countries_ids',
        'class' => CountryColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'dst_countries_ids',
        'class' => CountryColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'src_contracts_ids',
        'class' => ContractColumn::className(),
        'filterByServiceTrunkIds' => $filterModel->src_logical_trunks_ids,
        'filterByServerIds' => $filterModel->server_ids,
        'filterByTrunkIds' => $filterModel->src_physical_trunks_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'dst_contracts_ids',
        'class' => ContractColumn::className(),
        'filterByServiceTrunkIds' => $filterModel->dst_logical_trunks_ids,
        'filterByServerIds' => $filterModel->server_ids,
        'filterByTrunkIds' => $filterModel->dst_physical_trunks_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'src_regions_ids',
        'class' => RegionColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'countryCodes' => $filterModel->src_countries_ids ?: RegionColumn::EMPTY_VALUE_ID,
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'dst_regions_ids',
        'class' => RegionColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'countryCodes' => $filterModel->dst_countries_ids ?: RegionColumn::EMPTY_VALUE_ID,
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'src_cities_ids',
        'class' => CityColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'countryCodes' => $filterModel->src_countries_ids ?: ($filterModel->src_regions_ids ? null : CityColumn::EMPTY_VALUE_ID),
        'regionIds' => $filterModel->src_regions_ids ?: ($filterModel->src_countries_ids ? null : CityColumn::EMPTY_VALUE_ID),
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'dst_cities_ids',
        'class' => CityColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'countryCodes' => $filterModel->dst_countries_ids ?: ($filterModel->dst_regions_ids ? null : CityColumn::EMPTY_VALUE_ID),
        'regionIds' => $filterModel->dst_regions_ids ?: ($filterModel->dst_countries_ids ? null : CityColumn::EMPTY_VALUE_ID),
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'src_destinations_ids',
        'class' => DestinationColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'dst_destinations_ids',
        'class' => DestinationColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'src_number_type_ids',
        'class' => NdcTypeColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => false,
    ],
    [
        'attribute' => 'dst_number_type_ids',
        'class' => NdcTypeColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => false,
    ],
    [
        'attribute' => 'is_exclude_internal_trunk_term',
        'class' => CheckboxColumn::className(),
    ],
    [
        'attribute' => 'is_exclude_internal_trunk_orig',
        'class' => CheckboxColumn::className(),
    ],
    [
        'attribute' => 'session_time',
        'class' => IntegerRangeColumn::className(),
        'options' => [
            'min' => 0,
        ],
    ],
    [
        'attribute' => 'is_success_calls',
        'class' => CheckboxColumn::className(),
    ],
    [
        'attribute' => 'disconnect_causes',
        'class' => DisconnectCauseColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'currency',
        'class' => CurrencyColumn::className(),
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'group_period',
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
        'class' => ConstructColumn::className(),
        'filterOptions' => [
            'class' => ' col-sm-4'
        ],
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'filter' => $filterModel->getFilterGroups(),
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'aggr',
        'class' => ConstructColumn::className(),
        'filterOptions' => [
            'class' => ' col-sm-4'
        ],
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'filter' => $filterModel->getAggrGroups(),
        'isWithEmpty' => false,
    ],
];
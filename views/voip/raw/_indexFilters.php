<?php
/**
 * Фильтры для отчета по calls_raw
 *
 * @var CallsRawFilter $filterModel
 */

use app\classes\grid\column\billing\ContractColumn;
use app\classes\grid\column\billing\DisconnectCauseColumn;
use app\classes\grid\column\billing\MarketPlaceColumn;
use app\classes\grid\column\billing\ServerColumn;
use app\classes\grid\column\billing\ServiceTrunkColumn;
use app\classes\grid\column\billing\TrafficTypeColumn;
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

$filters = [
    [
        'attribute' => 'connect_time',
        'class' => $filterModel->isNewVersion && $filterModel->isPreFetched ?
            DateRangeDoubleColumn::class : DateTimeRangeDoubleColumn::class,
    ],
    [
        'attribute' => 'marketPlaceId',
        'class' => MarketPlaceColumn::class,
    ],
    [
        'attribute' => 'server_ids',
        'isWithEmpty' => false,
        'class' => ServerColumn::class,
        'value' => $filterModel->server_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'trafficType',
        'class' => TrafficTypeColumn::class,
    ],
    [
        'attribute' => 'account_id',
        'class' => StringColumn::class,
        'filterInputOptions' => [
            'title' => 'Допустимы только цифры (любая последовательность цифр, в том числе пустая строка)'
        ],
    ],
    [
        'class' => WithEmptyFilterColumn::class,
        'filterOptions' => [
            'class' => 'no_display'
        ],
    ],
    [
        'class' => WithEmptyFilterColumn::class,
        'filterOptions' => [
            'class' => 'no_display'
        ],
    ],
    [
        'class' => WithEmptyFilterColumn::class,
        'filterOptions' => [
            'class' => 'no_display'
        ],
    ],
    [
        'attribute' => 'src_number',
        'class' => StringColumn::class,
        'filterOptions' => [
            'title' => 'Допустимы цифры, _ или . (одна любая цифра), % или * (любая последовательность цифр, в том числе пустая строка)'
        ],
    ],
    [
        'attribute' => 'dst_number',
        'class' => StringColumn::class,
        'filterOptions' => [
            'title' => 'Допустимы цифры, _ или . (одна любая цифра), % или * (любая последовательность цифр, в том числе пустая строка)'
        ],
    ],
    [
        'attribute' => 'src_trunk_group_ids',
        'class' => TrunkGroupColumn::class,
        'filterByServerIds' => $filterModel->server_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false
    ],
    [
        'attribute' => 'dst_trunk_group_ids',
        'class' => TrunkGroupColumn::class,
        'filterByServerIds' => $filterModel->server_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false
    ],
    [
        'attribute' => 'src_physical_trunks_ids',
        'class' => TrunkColumn::class,
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
        'class' => TrunkColumn::class,
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
        'class' => OperatorColumn::class,
        'label' => $filterModel->getAttributeLabel('src_operator_ids') . '*',
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'countryCode' => $filterModel->src_countries_ids ?: OperatorColumn::EMPTY_VALUE_ID,
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'dst_operator_ids',
        'class' => OperatorColumn::class,
        'label' => $filterModel->getAttributeLabel('dst_operator_ids') . '*',
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'countryCode' => $filterModel->dst_countries_ids ?: OperatorColumn::EMPTY_VALUE_ID,
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'src_number_type_ids',
        'class' => NdcTypeColumn::class,
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => false,
    ],
    [
        'attribute' => 'dst_number_type_ids',
        'class' => NdcTypeColumn::class,
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => false,
    ],
    [
        'attribute' => 'src_logical_trunks_ids',
        'class' => ServiceTrunkColumn::class,
        'filterByServerIds' => $filterModel->server_ids,
        'filterByContractIds' => $filterModel->src_contracts_ids,
        'filterByTrunkIds' => $filterModel->src_physical_trunks_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ]
    ],
    [
        'attribute' => 'dst_logical_trunks_ids',
        'class' => ServiceTrunkColumn::class,
        'filterByContractIds' => $filterModel->dst_contracts_ids,
        'filterByServerIds' => $filterModel->server_ids,
        'filterByTrunkIds' => $filterModel->dst_physical_trunks_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    
    [
        'attribute' => 'src_contracts_ids',
        'class' => ContractColumn::class,
        'filterByServiceTrunkIds' => $filterModel->src_logical_trunks_ids,
        'filterByServerIds' => $filterModel->server_ids,
        'filterByTrunkIds' => $filterModel->src_physical_trunks_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'dst_contracts_ids',
        'class' => ContractColumn::class,
        'filterByServiceTrunkIds' => $filterModel->dst_logical_trunks_ids,
        'filterByServerIds' => $filterModel->server_ids,
        'filterByTrunkIds' => $filterModel->dst_physical_trunks_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'src_regions_ids',
        'class' => RegionColumn::class,
        'label' => $filterModel->getAttributeLabel('src_regions_ids') . '*',
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'countryCodes' => $filterModel->src_countries_ids ?: RegionColumn::EMPTY_VALUE_ID,
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'dst_regions_ids',
        'class' => RegionColumn::class,
        'label' => $filterModel->getAttributeLabel('dst_regions_ids') . '*',
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'countryCodes' => $filterModel->dst_countries_ids ?: RegionColumn::EMPTY_VALUE_ID,
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'src_countries_ids',
        'class' => CountryColumn::class,
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'src_exclude_country',
        'class' => CheckboxColumn::class,
    ],
    [
        'attribute' => 'src_cities_ids',
        'class' => CityColumn::class,
        'label' => $filterModel->getAttributeLabel('src_cities_ids') . '**',
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
        'class' => CityColumn::class,
        'label' => $filterModel->getAttributeLabel('dst_cities_ids') . '**',
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
        'class' => DestinationColumn::class,
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'dst_destinations_ids',
        'class' => DestinationColumn::class,
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'dst_countries_ids',
        'class' => CountryColumn::class,
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ], 
    [
        'attribute' => 'dst_exclude_country',
        'class' => CheckboxColumn::class,
    ],
    [
        'attribute' => 'session_time',
        'class' => IntegerRangeColumn::class,
        'options' => [
            'min' => 0,
        ],
    ],
    [
        'attribute' => 'is_success_calls',
        'class' => CheckboxColumn::class,
    ],
    [
        'attribute' => 'disconnect_causes',
        'class' => DisconnectCauseColumn::class,
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'currency',
        'class' => CurrencyColumn::class,
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'group_period',
        'class' => ConstructColumn::class,
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
        'class' => ConstructColumn::class,
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
        'class' => ConstructColumn::class,
        'filterOptions' => [
            'class' => ' col-sm-4'
        ],
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'filter' => $filterModel->getAggrGroups(),
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'is_exclude_internal_trunk_term',
        'class' => CheckboxColumn::class,
    ],
    [
        'attribute' => 'is_exclude_internal_trunk_orig',
        'class' => CheckboxColumn::class,
    ],
];

if ($exceptFilters = $filterModel->getExceptFilters()) {
    foreach ($filters as &$filter) {
        if (in_array($filter['attribute'], $exceptFilters)) {
            $filter = [
                'class' => WithEmptyFilterColumn::class,
                'filterOptions' => [
                    'class' => 'no_display'
                ],
            ];
        }
    }
}

return $filters;
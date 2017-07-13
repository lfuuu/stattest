<?php
/**
 * Фильтры для отчета по calls_raw
 *
 * @var CallsRawFilter $filterModel
 */

use app\classes\grid\column\billing\ContractColumn;
use app\classes\grid\column\billing\DisconnectCauseColumn;
use app\classes\grid\column\billing\ServerColumn;
use app\classes\grid\column\billing\ServiceTrunkColumn;
use app\classes\grid\column\billing\TrunkColumn;
use app\classes\grid\column\universal\CheckboxColumn;
use app\classes\grid\column\universal\ConstructColumn;
use app\classes\grid\column\universal\CurrencyColumn;
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
        'attribute' => 'src_physical_trunks_ids',
        'label' => 'Физический транк-оригинатор',
        'class' => TrunkColumn::className(),
        'filterByServerIds' => $filterModel->server_ids,
        'filterByServiceTrunkIds' => $filterModel->src_logical_trunks_ids,
        'filterByContractIds' => $filterModel->src_contracts_ids,
        'filterByShowInStat' => false,
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false
    ],
    [
        'attribute' => 'src_number',
        'label' => 'Маска номера А',
        'class' => StringColumn::className(),
        'filterOptions' => [
            'title' => 'Допустимы цифры, _ или . (одна любая цифра), % или * (любая последовательность цифр, в том числе пустая строка)'
        ],
    ],
    [
        'attribute' => 'dst_number',
        'label' => 'Маска номера В',
        'class' => StringColumn::className(),
        'filterOptions' => [
            'title' => 'Допустимы цифры, _ или . (одна любая цифра), % или * (любая последовательность цифр, в том числе пустая строка)'
        ],
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
        'attribute' => 'src_logical_trunks_ids',
        'label' => 'Логический транк-оригинатор',
        'class' => ServiceTrunkColumn::className(),
        'filterByServerIds' => $filterModel->server_ids,
        'filterByContractIds' => $filterModel->src_contracts_ids,
        'filterByTrunkIds' => $filterModel->src_physical_trunks_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ]
    ],
    [
        'attribute' => 'src_operator_ids',
        'label' => 'Оператор номера А',
        'class' => OperatorColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'dst_operator_ids',
        'label' => 'Оператор номера В',
        'class' => OperatorColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
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
        'attribute' => 'src_contracts_ids',
        'label' => 'Договор номера А',
        'class' => ContractColumn::className(),
        'filterByServiceTrunkIds' => $filterModel->src_logical_trunks_ids,
        'filterByServerIds' => $filterModel->server_ids,
        'filterByTrunkIds' => $filterModel->src_physical_trunks_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'src_countries_ids',
        'label' => 'Страна номера А',
        'class' => CountryColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'dst_countries_ids',
        'label' => 'Страна номера B',
        'class' => CountryColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
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
        'attribute' => 'dst_physical_trunks_ids',
        'label' => 'Физический транк-терминатор',
        'class' => TrunkColumn::className(),
        'filterByServerIds' => $filterModel->server_ids,
        'filterByServiceTrunkIds' => $filterModel->dst_logical_trunks_ids,
        'filterByContractIds' => $filterModel->dst_contracts_ids,
        'filterByShowInStat' => false,
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false
    ],
    [
        'attribute' => 'src_regions_ids',
        'label' => 'Регион номера А',
        'class' => RegionColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'countryCodes' => $filterModel->src_countries_ids,
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'dst_regions_ids',
        'label' => 'Регион номера B',
        'class' => RegionColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'countryCodes' => $filterModel->dst_countries_ids,
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'is_success_calls',
        'label' => 'Только успешные попытки',
        'class' => CheckboxColumn::className(),
    ],
    [
        'attribute' => 'dst_logical_trunks_ids',
        'label' => 'Логический транк-терминатор',
        'class' => ServiceTrunkColumn::className(),
        'filterByContractIds' => $filterModel->dst_contracts_ids,
        'filterByServerIds' => $filterModel->server_ids,
        'filterByTrunkIds' => $filterModel->dst_physical_trunks_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'src_cities_ids',
        'label' => 'Город номера А',
        'class' => CityColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'countryCodes' => $filterModel->src_countries_ids,
        'regionIds' => $filterModel->src_regions_ids,
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'dst_cities_ids',
        'label' => 'Город номера B',
        'class' => CityColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'countryCodes' => $filterModel->dst_countries_ids,
        'regionIds' => $filterModel->dst_regions_ids,
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => true,
    ],
    [
        'class' => WithEmptyFilterColumn::className(),
    ],
    [
        'attribute' => 'dst_contracts_ids',
        'label' => 'Договор номера B',
        'class' => ContractColumn::className(),
        'filterByServiceTrunkIds' => $filterModel->dst_logical_trunks_ids,
        'filterByServerIds' => $filterModel->server_ids,
        'filterByTrunkIds' => $filterModel->dst_physical_trunks_ids,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'src_destinations_ids',
        'label' => 'Направление номера А',
        'class' => DestinationColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
    ],
    [
        'attribute' => 'dst_destinations_ids',
        'label' => 'Направление номера В',
        'class' => DestinationColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
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
        'attribute' => 'src_number_type_ids',
        'label' => 'Тип номера А',
        'class' => NdcTypeColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => false,
    ],
    [
        'attribute' => 'dst_number_type_ids',
        'label' => 'Тип номера В',
        'class' => NdcTypeColumn::className(),
        'filterInputOptions' => [
            'multiple' => true,
        ],
        'isWithEmpty' => false,
        'isWithNullAndNotNull' => false,
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
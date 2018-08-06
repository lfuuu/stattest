<?php
/**
 * Список универсальных услуг
 *
 * @var \app\classes\BaseView $this
 * @var AccountTariffFilter $filterModel
 */

use app\classes\grid\column\CurrencyColumn;
use app\classes\grid\column\universal\BeautyLevelColumn;
use app\classes\grid\column\universal\CityColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\OrganizationColumn;
use app\classes\grid\column\universal\RegionColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\UserColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\nnp\column\NdcTypeColumn;
use app\modules\uu\column\DatacenterColumn;
use app\modules\uu\column\InfrastructureLevelColumn;
use app\modules\uu\column\InfrastructureProjectColumn;
use app\modules\uu\column\TariffPeriodColumn;
use app\modules\uu\column\TariffStatusColumn;
use app\modules\uu\filter\AccountTariffFilter;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffHeap;
use app\modules\uu\models\ServiceType;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;

$serviceType = $filterModel->getServiceType();

// базовые столбцы
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::className(),
        'template' => '{update}',
        'buttons' => [
            'update' => function ($url, AccountTariff $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
    [
        'label' => Yii::t('tariff', 'Universal services'),
        'attribute' => 'tariff_period_id',
        'class' => TariffPeriodColumn::className(),
        'serviceTypeId' => $serviceType->id,
        'format' => 'html',
        'value' => function (AccountTariff $accountTariff) {
            return Html::encode($accountTariff->getName(false));
        },
    ],
];

// Колонка "Дата включения на тестовый тариф"
$dateTestTariffColumn = null;

// Колонка "Ак. менеджер"
$accountManagerColumn = null;

// Колонка "Дата продажи"
$dateSaleColumn = null;

// Колонка "Дата допродажи"
$dateBeforeSaleColumn = null;

// Колонка "Дата отключения"
$dateDisconnectTariffColumn = null;

if (in_array($serviceType->id, [ServiceType::ID_VPBX, ServiceType::ID_VOIP, ServiceType::ID_CALL_CHAT])) {
    if ($serviceType->id == ServiceType::ID_VOIP) {
        $dateTestTariffColumn = [
            'label' => 'Дата включения на тестовый тариф, utc',
            'attribute' => 'test_connect_date',
            'class' => DateRangeDoubleColumn::className(),
            'value' => function (AccountTariff $accountTariff) {
                /** @var AccountTariffHeap $accountTariffHeap*/
                $accountTariffHeap = $accountTariff->getAccountTariffHeap()
                    ->one();
                return ($accountTariffHeap && $accountTariffHeap->test_connect_date) ?
                    $accountTariffHeap->test_connect_date: '';
            },
        ];
    }

    $accountManagerColumn = [
        'label' => 'Ак. менеджер',
        'attribute' => 'account_manager_name',
        'class' => UserColumn::className(),
        'value' => function (AccountTariff $accountTariff) {
            return $accountTariff->clientAccount->contract->getAccountManagerName();
        },
    ];

    $dateSaleColumn = [
        'label' => 'Дата продажи, utc',
        'attribute' => 'date_sale',
        'class' => DateRangeDoubleColumn::className(),
        'value' => function(AccountTariff $accountTariff) {
            /** @var AccountTariffHeap $accountTariffHeap*/
            $accountTariffHeap = $accountTariff->getAccountTariffHeap()
                ->one();
            return ($accountTariffHeap && $accountTariffHeap->date_sale) ?
                $accountTariffHeap->date_sale : '';
        },
    ];

    $dateBeforeSaleColumn = [
        'label' => 'Дата допродажи, utc',
        'attribute' => 'date_before_sale',
        'class' => DateRangeDoubleColumn::className(),
        'value' => function(AccountTariff $accountTariff) {
            /** @var AccountTariffHeap $accountTariffHeap*/
            $accountTariffHeap = $accountTariff->getAccountTariffHeap()
                ->one();
            return ($accountTariffHeap && $accountTariffHeap->date_before_sale) ?
                $accountTariffHeap->date_before_sale : '';
        },
    ];

    $dateDisconnectTariffColumn = [
        'label' => 'Дата отключения, utc',
        'attribute' => 'disconnect_date',
        'class' => DateRangeDoubleColumn::className(),
        'value' => function(AccountTariff $accountTariff) {
            /** @var AccountTariffHeap $accountTariffHeap*/
            $accountTariffHeap = $accountTariff->getAccountTariffHeap()
                ->one();
            return ($accountTariffHeap && $accountTariffHeap->disconnect_date) ?
                $accountTariffHeap->disconnect_date : '';
        },
    ];
}

if ($serviceTypeId = $serviceType->isPackage()) {
    $columns[] = [
        'label' => 'Тариф основной услуги',
        'attribute' => 'prev_account_tariff_tariff_id',
        'class' => TariffPeriodColumn::className(),
        'serviceTypeId' => $serviceTypeId,
        'format' => 'html',
        'value' => function (AccountTariff $accountTariff) {
            $prevAccountTariff = $accountTariff->prevAccountTariff;
            $tariffPeriod = $prevAccountTariff ? $prevAccountTariff->tariffPeriod : null;
            return $tariffPeriod ? $tariffPeriod->getName() : null;
        },
    ];
}

$columns = array_merge($columns, [
    [
        'label' => 'Включая НДС',
        'attribute' => 'tariff_is_include_vat',
        'class' => YesNoColumn::className(),
        'value' => function (AccountTariff $accountTariff) {
            $tariffPeriod = $accountTariff->tariffPeriod;
            $tariff = $tariffPeriod ? $tariffPeriod->tariff : null;

            return $tariff ? $tariff->is_include_vat : null;
        }
    ],
    [
        'label' => 'Постоплата',
        'attribute' => 'tariff_is_postpaid',
        'class' => YesNoColumn::className(),
        'value' => function (AccountTariff $accountTariff) {
            $tariffPeriod = $accountTariff->tariffPeriod;
            $tariff = $tariffPeriod ? $tariffPeriod->tariff : null;

            return $tariff ? $tariff->is_postpaid : null;
        }
    ],
    [
        'label' => 'Страна',
        'attribute' => 'tariff_country_id',
        'format' => 'html',
        'class' => CountryColumn::className(),
        'isAddLink' => false,
        'contentOptions' => [
            'class' => 'nowrap',
        ],
        'value' => function (AccountTariff $accountTariff) {
            $tariffPeriod = $accountTariff->tariffPeriod;
            if (!$tariffPeriod) {
                return '';
            }

            $maxCount = 2;
            $tariff = $tariffPeriod->tariff;
            $tariffCountries = $tariff->tariffCountries;
            $count = count($tariffCountries);
            if ($count <= $maxCount) {
                return implode('<br/>', $tariffCountries);
            }

            $maxCount--;

            return sprintf(
                '%s<br/><abbr title="%s">… %d…</abbr>',
                implode('<br/>', array_slice($tariffCountries, 0, $maxCount)),
                implode(PHP_EOL, array_slice($tariffCountries, $maxCount)),
                $count - $maxCount
            );
        }
    ],
    [
        'attribute' => 'tariff_currency_id',
        'class' => CurrencyColumn::className(),
        'value' => function (AccountTariff $accountTariff) {
            $tariffPeriod = $accountTariff->tariffPeriod;
            $tariff = $tariffPeriod ? $tariffPeriod->tariff : null;

            return $tariff ? $tariff->currency_id : null;
        }
    ],
    [
        'label' => 'Организации',
        'attribute' => 'tariff_organization_id',
        'format' => 'html',
        'class' => OrganizationColumn::className(),
        'contentOptions' => [
            'class' => 'nowrap',
        ],
        'value' => function (AccountTariff $accountTariff) {
            $tariffPeriod = $accountTariff->tariffPeriod;
            $tariff = $tariffPeriod ? $tariffPeriod->tariff : null;

            return $tariff ? $tariff->getOrganizationsString() : '';
        }
    ],
    [
        'label' => 'По умолчанию',
        'attribute' => 'tariff_is_default',
        'class' => YesNoColumn::className(),
        'value' => function (AccountTariff $accountTariff) {
            $tariffPeriod = $accountTariff->tariffPeriod;
            $tariff = $tariffPeriod ? $tariffPeriod->tariff : null;

            return $tariff ? $tariff->is_default : null;
        }
    ],
    [
        'label' => 'Статус тарифа',
        'attribute' => 'tariff_status_id',
        'class' => TariffStatusColumn::className(),
        'serviceTypeId' => $serviceType->id,
        'format' => 'html',
        'value' => function (AccountTariff $accountTariff) {
            $tariffPeriod = $accountTariff->tariffPeriod;
            return $tariffPeriod ?
                $tariffPeriod->tariff->tariff_status_id :
                null;
        },
    ],
    [
        'attribute' => 'client_account_id',
        'class' => IntegerColumn::className(),
        'format' => 'html',
        'value' => function (AccountTariff $accountTariff) {
            return $accountTariff->clientAccount->getLink();
        },
    ],
    [
        'attribute' => 'region_id',
        'class' => RegionColumn::className(),
    ],
    [
        'attribute' => 'comment',
        'format' => 'html',
        'value' => function (AccountTariff $accountTariff) {
            return nl2br(Html::encode($accountTariff->comment));
        },
    ],
    [
        'attribute' => 'tariff_period_utc',
        'class' => DateRangeDoubleColumn::className(),
    ],
    [
        'attribute' => 'account_log_period_utc',
        'class' => DateRangeDoubleColumn::className(),
    ],
]);

// столбцы для конкретной услуги
if ($serviceType) {
    switch ($serviceType->id) {

        case ServiceType::ID_VPBX:
            $columns[] = [
                'attribute' => 'is_unzipped',
                'class' => YesNoColumn::className(),
            ];
            $columns[] = $dateSaleColumn;
            $columns[] = $dateBeforeSaleColumn;
            $columns[] = $dateDisconnectTariffColumn;
            $columns[] = $accountManagerColumn;
            break;

        case ServiceType::ID_VOIP:
            $columns[] = [
                'attribute' => 'city_id',
                'class' => CityColumn::className(),
            ];
            $columns[] = [
                'attribute' => 'voip_number',
                'class' => StringColumn::className(),
                'filterOptions' => [
                    'title' => 'Допустимы цифры, _ или . (одна любая цифра), % или * (любая последовательность цифр, в том числе пустая строка)',
                ],
            ];
            $columns[] = [
                'label' => 'Красивость',
                'attribute' => 'beauty_level',
                'class' => BeautyLevelColumn::className(),
                'value' => function (AccountTariff $accountTariff) {
                    return $accountTariff->number->beauty_level;
                },
            ];
            $columns[] = [
                'label' => 'Тип NDC',
                'attribute' => 'number_ndc_type_id',
                'class' => NdcTypeColumn::className(),
                'value' => function (AccountTariff $accountTariff) {
                    return $accountTariff->number->ndc_type_id;
                },
            ];
            $columns[] = $dateTestTariffColumn;
            $columns[] = $dateSaleColumn;
            $columns[] = $dateBeforeSaleColumn;
            $columns[] = $dateDisconnectTariffColumn;
            $columns[] = $accountManagerColumn;
            break;

        case ServiceType::ID_CALL_CHAT:
            $columns[] = $dateSaleColumn;
            $columns[] = $dateBeforeSaleColumn;
            $columns[] = $dateDisconnectTariffColumn;
            $columns[] = $accountManagerColumn;
            break;

        case ServiceType::ID_VOIP_PACKAGE_CALLS:
            $columns[] = [
                'attribute' => 'city_id',
                'class' => CityColumn::className(),
            ];
            break;

        case ServiceType::ID_INFRASTRUCTURE:
            $columns[] = [
                'attribute' => 'infrastructure_project',
                'class' => InfrastructureProjectColumn::className(),
            ];
            $columns[] = [
                'attribute' => 'infrastructure_level',
                'class' => InfrastructureLevelColumn::className(),
            ];
            $columns[] = [
                'attribute' => 'datacenter_id',
                'class' => DatacenterColumn::className(),
            ];
            $columns[] = [
                'attribute' => 'city_id',
                'class' => CityColumn::className(),
            ];
            $columns[] = [
                'attribute' => 'price',
                'class' => IntegerRangeColumn::className(),
            ];
            break;
    }
}

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'extraButtons' => $serviceType->id == ServiceType::ID_VOIP_PACKAGE_CALLS ?
        '' :
        $this->render('//layouts/_buttonCreate', ['url' => AccountTariff::getUrlNew($serviceType->id)]),
    'columns' => $columns,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);

// Рендеринг формы для применения групповых действий к отфильтрованным элементам
echo $this->render('_indexMainGroupAction', [
    'filterModel' => $filterModel,
]);
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
use app\modules\uu\column\ContragentColumn;
use app\modules\uu\column\DatacenterColumn;
use app\modules\uu\column\InfrastructureLevelColumn;
use app\modules\uu\column\InfrastructureProjectColumn;
use app\modules\uu\column\TariffPeriodColumn;
use app\modules\uu\column\TariffStatusColumn;
use app\modules\uu\column\PriceLevelColumn;
use app\modules\uu\filter\AccountTariffFilter;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTrouble;
use app\modules\uu\models\ServiceType;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;

$serviceType = $filterModel->getServiceType();

// базовые столбцы
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::class,
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
        'class' => TariffPeriodColumn::class,
        'serviceTypeId' => $serviceType ? $serviceType->id : '',
        'withTariffId' => true,
        'currency' => $filterModel->tariff_currency_id ?: null,
        'format' => 'html',
        'value' => function (AccountTariff $accountTariff) {
            return Html::encode($accountTariff->getName(false, false, true));
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

if ($serviceType && in_array($serviceType->id, [ServiceType::ID_VPBX, ServiceType::ID_VOIP, ServiceType::ID_CALL_CHAT])) {
    if ($serviceType->id == ServiceType::ID_VOIP) {
        $dateTestTariffColumn = [
            'label' => 'Дата включения на тестовый тариф, utc',
            'attribute' => 'test_connect_date',
            'class' => DateRangeDoubleColumn::class,
            'value' => function (AccountTariff $accountTariff) {
                $accountTariffHeap = $accountTariff->accountTariffHeap;
                return ($accountTariffHeap && $accountTariffHeap->test_connect_date) ?
                    $accountTariffHeap->test_connect_date : '';
            },
        ];
    }

    $accountManagerColumn = [
        'label' => 'Ак. менеджер',
        'attribute' => 'account_manager_name',
        'class' => UserColumn::class,
        'value' => function (AccountTariff $accountTariff) {
            return $accountTariff->clientAccount->clientContractModel->getAccountManagerName();
        },
    ];

    $dateSaleColumn = [
        'label' => 'Дата продажи, utc',
        'attribute' => 'date_sale',
        'class' => DateRangeDoubleColumn::class,
        'value' => function (AccountTariff $accountTariff) {
            $accountTariffHeap = $accountTariff->accountTariffHeap;
            return ($accountTariffHeap && $accountTariffHeap->date_sale) ?
                $accountTariffHeap->date_sale : '';
        },
    ];

    $dateBeforeSaleColumn = [
        'label' => 'Дата допродажи, utc',
        'attribute' => 'date_before_sale',
        'class' => DateRangeDoubleColumn::class,
        'value' => function (AccountTariff $accountTariff) {
            $accountTariffHeap = $accountTariff->accountTariffHeap;
            return ($accountTariffHeap && $accountTariffHeap->date_before_sale) ?
                $accountTariffHeap->date_before_sale : '';
        },
    ];

    $dateDisconnectTariffColumn = [
        'label' => 'Дата отключения, utc',
        'attribute' => 'disconnect_date',
        'class' => DateRangeDoubleColumn::class,
        'value' => function (AccountTariff $accountTariff) {
            $accountTariffHeap = $accountTariff->accountTariffHeap;
            return ($accountTariffHeap && $accountTariffHeap->disconnect_date) ?
                $accountTariffHeap->disconnect_date : '';
        },
    ];
}

if ($serviceType && $serviceTypeId = $serviceType->isPackage()) {
    $columns[] = [
        'label' => 'Тариф основной услуги',
        'attribute' => 'prev_account_tariff_tariff_id',
        'class' => TariffPeriodColumn::class,
        'serviceTypeId' => $serviceTypeId,
        'format' => 'html',
        'value' => function (AccountTariff $accountTariff) {
            $prevAccountTariff = $accountTariff->prevAccountTariff;
            $tariffPeriod = $prevAccountTariff ? $prevAccountTariff->tariffPeriod : null;
            return $tariffPeriod ? $tariffPeriod->getNameWithTariffId() : null;
        },
    ];
}

$columns = array_merge($columns, [
    [
        'label' => 'Включая НДС',
        'attribute' => 'tariff_is_include_vat',
        'class' => YesNoColumn::class,
        'value' => function (AccountTariff $accountTariff) {
            $tariffPeriod = $accountTariff->tariffPeriod;
            $tariff = $tariffPeriod ? $tariffPeriod->tariff : null;

            return $tariff ? $tariff->is_include_vat : null;
        }
    ],
    [
        'label' => 'Юр. тип',
        'attribute' => 'contragent_type',
        'class' => ContragentColumn::class,
        'value' => function (AccountTariff $accountTariff) {
            return $accountTariff->clientAccount->contract->contragent->legal_type;
        },
    ],
    [
        'label' => 'Страна витрины',
        'attribute' => 'tariff_country_id',
        'format' => 'html',
        'class' => CountryColumn::class,
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
        'class' => CurrencyColumn::class,
        'value' => function (AccountTariff $accountTariff) {
            $tariffPeriod = $accountTariff->tariffPeriod;
            $tariff = $tariffPeriod ? $tariffPeriod->tariff : null;

            return $tariff ? $tariff->currency_id : null;
        }
    ],
    [
        'label' => 'Организации тарифа',
        'attribute' => 'tariff_organization_id',
        'format' => 'html',
        'class' => OrganizationColumn::class,
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
        'label' => 'Организация клиента',
        'attribute' => 'client_organization_id',
        'format' => 'html',
        'class' => OrganizationColumn::class,
        'contentOptions' => [
            'class' => 'nowrap',
        ],
        'value' => function (AccountTariff $accountTariff) {
            return $accountTariff->clientAccount->contract->organization->name;
        }
    ],
    [
        'label' => 'По умолчанию',
        'attribute' => 'tariff_is_default',
        'class' => YesNoColumn::class,
        'value' => function (AccountTariff $accountTariff) {
            $tariffPeriod = $accountTariff->tariffPeriod;
            $tariff = $tariffPeriod ? $tariffPeriod->tariff : null;

            return $tariff ? $tariff->is_default : null;
        }
    ],
    [
        'label' => 'Статус тарифа',
        'attribute' => 'tariff_status_id',
        'class' => TariffStatusColumn::class,
        'serviceTypeId' => $serviceType ? $serviceType->id : '',
        'format' => 'html',
        'value' => function (AccountTariff $accountTariff) {
            $tariffPeriod = $accountTariff->tariffPeriod;
            return $tariffPeriod ?
                $tariffPeriod->tariff->tariff_status_id :
                null;
        },
    ],
    [
        'label' => 'Уровень цен',
        'attribute' => 'price_level',
        'class' => PriceLevelColumn::class,
        'value' => function (AccountTariff $accountTariff) {
            return $accountTariff->clientAccount->price_level;
        },
    ],
    [
        'attribute' => 'client_account_id',
        'class' => IntegerColumn::class,
        'format' => 'html',
        'value' => function (AccountTariff $accountTariff) {
            return $accountTariff->clientAccount->getLink();
        },
    ],
    [
        'attribute' => 'region_id',
        'class' => RegionColumn::class,
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
        'class' => DateRangeDoubleColumn::class,
    ],
    [
        'attribute' => 'account_log_period_utc',
        'class' => DateRangeDoubleColumn::class,
    ],
]);

// столбцы для конкретной услуги
if ($serviceType) {
    switch ($serviceType->id) {

        case ServiceType::ID_VPBX:
            $columns[] = [
                'attribute' => 'is_unzipped',
                'class' => YesNoColumn::class,
            ];
            $columns[] = $dateSaleColumn;
            $columns[] = $dateBeforeSaleColumn;
            $columns[] = $dateDisconnectTariffColumn;
            $columns[] = $accountManagerColumn;
            break;

        case ServiceType::ID_VOIP:
            $columns[] = [
                'attribute' => 'city_id',
                'class' => CityColumn::class,
            ];
            $columns[] = [
                'attribute' => 'voip_number',
                'class' => StringColumn::class,
                'filterOptions' => [
                    'title' => 'Допустимы цифры, _ или . (одна любая цифра), % или * (любая последовательность цифр, в том числе пустая строка)',
                ],
            ];
            $columns[] = [
                'label' => 'Красивость',
                'attribute' => 'beauty_level',
                'class' => BeautyLevelColumn::class,
                'value' => function (AccountTariff $accountTariff) {
                    return $accountTariff->number->beauty_level;
                },
            ];
            $columns[] = [
                'label' => 'Тип NDC',
                'attribute' => 'number_ndc_type_id',
                'class' => NdcTypeColumn::class,
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
                'class' => CityColumn::class,
            ];

            $columns[] = [
                'label' => 'Тип NDC',
                'attribute' => 'number_ndc_type_id',
                'class' => NdcTypeColumn::class,
                'isWithNullAndNotNull' => false,
                'value' => function (AccountTariff $accountTariff) {
                    return $accountTariff->prevAccountTariff->number->ndc_type_id;
                },
            ];
            break;

        case ServiceType::ID_INFRASTRUCTURE:
            $columns[] = [
                'attribute' => 'infrastructure_project',
                'class' => InfrastructureProjectColumn::class,
            ];
            $columns[] = [
                'attribute' => 'infrastructure_level',
                'class' => InfrastructureLevelColumn::class,
            ];
            $columns[] = [
                'attribute' => 'datacenter_id',
                'class' => DatacenterColumn::class,
            ];
            $columns[] = [
                'attribute' => 'city_id',
                'class' => CityColumn::class,
            ];
            $columns[] = [
                'attribute' => 'price',
                'class' => IntegerRangeColumn::class,
            ];
            break;
    }
}

// Добавление колонки "Заявка ЛИД" для услуг с типами: ВАТС, Телефония, Звонок-чат
if ($serviceType && in_array($serviceType->id, [ServiceType::ID_VPBX, ServiceType::ID_VOIP, ServiceType::ID_CALL_CHAT], true)) {
    $columns[] = [
        'label' => 'Заявка ЛИД',
        'attribute' => 'trouble_id',
        'format' => 'raw',
        'value' => function (AccountTariff $accountTariff) {
            $value = '';
            foreach ($accountTariff->accountTroubles as $accountTrouble) {
                /** @var AccountTrouble $accountTrouble */
                $value .= Html::a($accountTrouble->trouble_id, ["index.php?module=tt&action=view&id=$accountTrouble->trouble_id"]);
            }
            return $value;
        }
    ];
}

$dataProvider = $filterModel->search();

// показываем сумму, если есть поле с ценой
$headerColumns = [];
if (array_search('price', array_column($columns, 'attribute'))) {
    /** @var Query $query */
    $query = clone $dataProvider->query;
    $sum = $query->sum('price');
    $headerColumns[] = ['options' => ['colspan' => count($columns) - 2]];
    $headerColumns[] = ['content' => Yii::t('common', 'Summary') . ':'];
    $headerColumns[] = ['content' => $sum];
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'extraButtons' => $serviceType && $serviceType->id == ServiceType::ID_VOIP_PACKAGE_CALLS ?
        '' :
        $this->render('//layouts/_buttonCreate', ['url' => AccountTariff::getUrlNew($serviceType ? $serviceType->id : '')]),
    'columns' => $columns,
    'afterHeader' => [
        [
            'options' => ['class' => \kartik\grid\GridView::TYPE_WARNING],
            'columns' => $headerColumns,
        ]
    ],

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

if ($serviceType && in_array($serviceType->id, [ServiceType::ID_VOIP, ServiceType::ID_VOIP_PACKAGE_CALLS, ServiceType::ID_VOIP_PACKAGE_INTERNET_ROAMABILITY, ServiceType::ID_VOIP_PACKAGE_SMS])) {
    echo $this->render('_addPackage', [
        'filterModel' => $filterModel,
        'service_type_id' => $serviceType->id
    ]);
}
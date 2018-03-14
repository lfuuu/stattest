<?php
/**
 * Список универсальных тарифов
 *
 * @var \app\classes\BaseView $this
 * @var TariffFilter $filterModel
 */

use app\classes\grid\column\universal\CityColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\CurrencyColumn;
use app\classes\grid\column\universal\OrganizationColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\nnp\column\NdcTypeColumn;
use app\modules\nnp\models\PackageMinute;
use app\modules\nnp\models\PackagePrice;
use app\modules\nnp\models\PackagePricelist;
use app\modules\uu\column\TariffPersonColumn;
use app\modules\uu\column\TariffStatusColumn;
use app\modules\uu\column\TariffTagColumn;
use app\modules\uu\column\TariffVoipGroupColumn;
use app\modules\uu\filter\TariffFilter;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffOrganization;
use app\modules\uu\models\TariffResource;
use app\modules\uu\models\TariffVoipCity;
use app\modules\uu\models\TariffVoipNdcType;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$serviceType = $filterModel->getServiceType();
if (!$serviceType) {
    Yii::$app->session->setFlash('error', \Yii::t('common', 'Wrong ID'));
    return;
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tariffs'),
        ['label' => $this->title = $serviceType->name, 'url' => Url::to(['/uu/tariff', 'serviceTypeId' => $serviceType->id])],
    ],
]) ?>

<?php
// базовые столбцы
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::className(),
        'template' => '{update} {delete}',
        'buttons' => [
            'update' => function ($url, Tariff $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, Tariff $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionDrop', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::className(),
    ],
    [
        'label' => Yii::t('common', 'Price'),
        'value' => function (Tariff $tariff) {
            $tariffPeriods = $tariff->tariffPeriods;
            $tariffPeriod = reset($tariffPeriods);
            return sprintf('%s / %s, %s', $tariffPeriod->price_setup, $tariffPeriod->price_min, $tariffPeriod->price_per_period);
        },
    ],
    [
        'attribute' => 'currency_id',
        'class' => CurrencyColumn::className(),
    ],
    [
        'attribute' => 'country_id',
        'class' => CountryColumn::className(),
    ],
    [
        'attribute' => 'tariff_status_id',
        'serviceTypeId' => $serviceType->id,
        'class' => TariffStatusColumn::className(),
        'value' => function (Tariff $tariff) {
            return $tariff->status->name;
        },
        'isWithEmpty' => false,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'tariff_person_id',
        'class' => TariffPersonColumn::className(),
        'isWithEmpty' => false,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'tag_id',
        'class' => TariffTagColumn::className(),
    ],
    [
        'label' => 'Продл.',
        'attribute' => 'is_autoprolongation',
        'class' => YesNoColumn::className(),
    ],
    [
        'attribute' => 'is_charge_after_blocking',
        'class' => YesNoColumn::className(),
    ],
    [
        'attribute' => 'is_include_vat',
        'class' => YesNoColumn::className(),
    ],
    [
        'attribute' => 'is_default',
        'class' => YesNoColumn::className(),
    ],
];


if (!array_key_exists($serviceType->id, ServiceType::$packages)) {
    $columns[] = [
        'attribute' => 'is_postpaid',
        'class' => YesNoColumn::className(),
    ];
}

$columns[] = [
    'label' => Html::encode(Yii::t('models/' . TariffOrganization::tableName(), 'organization_id')),
    'attribute' => 'organization_id',
    'format' => 'html',
    'class' => OrganizationColumn::className(),
    'isAddLink' => false,
    'contentOptions' => [
        'class' => 'nowrap',
    ],
    'value' => function (Tariff $tariff) {
        return $tariff->getOrganizationsString();
    }
];

$cityColumn = [
    'label' => Html::encode(Yii::t('models/' . TariffVoipCity::tableName(), 'city_id')),
    'attribute' => 'voip_city_id',
    'format' => 'html',
    'class' => CityColumn::className(),
    'isAddLink' => false,
    'contentOptions' => [
        'class' => 'nowrap',
    ],
    'value' => function (Tariff $tariff) {
        $maxCount = 2;
        $voipCities = $tariff->voipCities;
        $count = count($voipCities);
        if ($count <= $maxCount) {
            return implode('<br/>', $voipCities);
        }

        return sprintf(
            '%s<br/><abbr title="%s">… %d…</abbr>',
            implode('<br/>', array_slice($voipCities, 0, $maxCount)),
            implode(PHP_EOL, array_slice($voipCities, $maxCount)),
            $count - $maxCount
        );
    }
];

$ndcTypeColumn = [
    'label' => Html::encode(Yii::t('models/' . TariffVoipNdcType::tableName(), 'ndc_type_id')),
    'attribute' => 'voip_ndc_type_id',
    'format' => 'html',
    'class' => NdcTypeColumn::className(),
    'isAddLink' => false,
    'contentOptions' => [
        'class' => 'nowrap',
    ],
    'value' => function (Tariff $tariff) {
        $maxCount = 2;
        $voipNdcTypes = $tariff->voipNdcTypes;
        $count = count($voipNdcTypes);
        if ($count <= $maxCount) {
            return implode('<br/>', $voipNdcTypes);
        }

        return sprintf(
            '%s<br/><abbr title="%s">… %d…</abbr>',
            implode('<br/>', array_slice($voipNdcTypes, 0, $maxCount)),
            implode(PHP_EOL, array_slice($voipNdcTypes, $maxCount)),
            $count - $maxCount
        );
    }
];

// столбцы для конкретной услуги
switch ($serviceType->id) {

    case ServiceType::ID_VPBX:
        break;

    case ServiceType::ID_VOIP:
        $columns[] = $cityColumn;
        $columns[] = $ndcTypeColumn;
        break;

    case ServiceType::ID_VOIP_PACKAGE_CALLS:
        $columns[] = $cityColumn;
        $columns[] = $ndcTypeColumn;

        $columns[] = [
            'label' => Html::encode(Yii::t('models/' . Tariff::tableName(), 'voip_group_id')),
            'attribute' => 'voip_group_id',
            'class' => TariffVoipGroupColumn::className(),
            'value' => function (Tariff $tariff) {
                return $tariff->voip_group_id;
            }
        ];

        $columns[] = [
            'label' => 'Предоплаченные минуты',
            'format' => 'html',
            'value' => function (Tariff $tariff) {
                $maxCount = 2;
                $packageMinutes = $tariff->getPackageMinutes()->limit($maxCount + 1)->all();
                $count = count($packageMinutes);
                if ($count > $maxCount) {
                    array_pop($packageMinutes);
                }

                $echoArray = array_map(function (PackageMinute $packageMinute) {
                    $destination = $packageMinute->destination;
                    return $packageMinute->minute . ' ' . Html::a($destination->name, $destination->getUrl());
                }, $packageMinutes);

                if ($count > $maxCount) {
                    $echoArray[] = '…';
                }

                return '<p>' . implode('</p><p>', $echoArray) . '</p>';
            },
            'options' => [
                'style' => 'min-width: 200px;',
            ],
        ];

        $columns[] = [
            'label' => 'Цена по направлениям',
            'format' => 'html',
            'value' => function (Tariff $tariff) {
                $maxCount = 2;
                $packagePrices = $tariff->getPackagePrices()->limit($maxCount + 1)->all();
                $count = count($packagePrices);
                if ($count > $maxCount) {
                    array_pop($packagePrices);
                }

                $echoArray = array_map(function (PackagePrice $packagePrice) {
                    $destination = $packagePrice->destination;
                    return $packagePrice->price . ' ' . Html::a($destination->name, $destination->getUrl());
                }, $packagePrices);

                if ($count > $maxCount) {
                    $echoArray[] = '…';
                }

                return '<p>' . implode('</p><p>', $echoArray) . '</p>';
            },
            'options' => [
                'style' => 'min-width: 200px;',
            ],
        ];

        $columns[] = [
            'label' => 'Прайслист с МГП',
            'format' => 'html',
            'value' => function (Tariff $tariff) {
                $maxCount = 2;
                $packagePricelists = $tariff->getPackagePricelists()->limit($maxCount + 1)->all();
                $count = count($packagePricelists);
                if ($count > $maxCount) {
                    array_pop($packagePricelists);
                }

                $echoArray = array_map(function (PackagePricelist $packagePricelist) {
                    $pricelist = $packagePricelist->pricelist;
                    return $pricelist ?
                        Html::a($pricelist->name, $pricelist->getUrl()) :
                        '';
                }, $packagePricelists);

                if ($count > $maxCount) {
                    $echoArray[] = '…';
                }

                return '<p>' . implode('</p><p>', $echoArray) . '</p>';
            },
            'options' => [
                'style' => 'min-width: 200px;',
            ],
        ];
        break;
}

// столбцы с ресурсами
$resources = Resource::findAll(['service_type_id' => $serviceType->id]);
foreach ($resources as $resource) {
    if (in_array($resource->id, [Resource::ID_VOIP_PACKAGE_CALLS, Resource::ID_VOIP_PACKAGE_CALLS])) {
        continue;
    }

    $columns[] = [
        'label' => Html::encode($resource->name . ($resource->unit ? ', ' . $resource->unit : '')),
        'value' => function (Tariff $tariff) use ($resource) {
            /** @var TariffResource $tariffResource */
            $tariffResource = $tariff->getTariffResource($resource->id)->one();
            if (!$tariffResource) {
                return '';
            }

            if ($resource->isNumber()) {
                return strpos($tariffResource->amount, '.') !== false ?
                    sprintf('%.2f', $tariffResource->amount) :
                    $tariffResource->amount;
            }

            return $tariffResource->amount ? '+' : '-';
        }
    ];
}

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => Tariff::getUrlNew($serviceType->id)]),
    'columns' => $columns,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);
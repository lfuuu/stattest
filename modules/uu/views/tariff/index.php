<?php
/**
 * Список универсальных тарифов
 *
 * @var BaseView $this
 * @var TariffFilter $filterModel
 */

use app\classes\BaseView;
use app\classes\grid\column\universal\CityColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\CurrencyColumn;
use app\classes\grid\column\universal\IntegerColumn;
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
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffCountry;
use app\modules\uu\models\TariffOrganization;
use app\modules\uu\models\TariffResource;
use app\modules\uu\models\TariffVoipCity;
use app\modules\uu\models\TariffVoipCountry;
use app\modules\uu\models\TariffVoipNdcType;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\Breadcrumbs;

$serviceType = $filterModel->getServiceType();
if (!$serviceType) {
    Yii::$app->session->setFlash('error', Yii::t('common', 'Wrong ID'));
    return;
}
?>

<?= Breadcrumbs::widget([
    'links' => [

        [
            'label' => Yii::t('tariff', 'Universal tariffs') .
                $this->render('//layouts/_helpConfluence', Tariff::getHelpConfluence()),
            'encode' => false,
        ],

        ['label' => $this->title = $serviceType->name, 'url' => Url::to(['/uu/tariff', 'serviceTypeId' => $serviceType->id])],

        [
            'label' => $this->render('//layouts/_helpConfluence', $serviceType->getHelpConfluence()),
            'encode' => false,
        ],
    ],
]) ?>

<?php
// базовые столбцы
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::class,
        'template' => '{update}',
        'buttons' => [
            'update' => function ($url, Tariff $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
    [
        'attribute' => 'id',
        'class' => IntegerColumn::class,
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::class,
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
        'class' => CurrencyColumn::class,
    ],
    [
        'label' => Html::encode(Yii::t('models/' . TariffCountry::tableName(), 'country_id')),
        'attribute' => 'country_id',
        'format' => 'html',
        'class' => CountryColumn::class,
        'isAddLink' => false,
        'contentOptions' => [
            'class' => 'nowrap',
        ],
        'value' => function (Tariff $tariff) {
            $maxCount = 2;
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
        },
    ],
    [
        'attribute' => 'tariff_status_id',
        'serviceTypeId' => $serviceType->id,
        'class' => TariffStatusColumn::class,
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
        'class' => TariffPersonColumn::class,
        'isWithEmpty' => false,
        'filterInputOptions' => [
            'multiple' => true,
        ],
    ],
    [
        'attribute' => 'tag_id',
        'class' => TariffTagColumn::class,
    ],
    [
        'label' => 'Продл.',
        'attribute' => 'is_autoprolongation',
        'class' => YesNoColumn::class,
    ],
    [
        'attribute' => 'is_charge_after_blocking',
        'class' => YesNoColumn::class,
    ],
    [
        'attribute' => 'is_include_vat',
        'class' => YesNoColumn::class,
    ],
    [
        'attribute' => 'is_default',
        'class' => YesNoColumn::class,
    ],
];


$columns[] = [
    'label' => Html::encode(Yii::t('models/' . TariffOrganization::tableName(), 'organization_id')),
    'attribute' => 'organization_id',
    'format' => 'html',
    'class' => OrganizationColumn::class,
    'isAddLink' => false,
    'contentOptions' => [
        'class' => 'nowrap',
    ],
    'value' => function (Tariff $tariff) {
        return $tariff->getOrganizationsString();
    }
];


$columns[] = [
    'label' => 'Страна номера',
//    'label' => Html::encode(Yii::t('models/' . TariffCountry::tableName(), 'voip_country_id')),
    'attribute' => 'voip_country_id',
    'format' => 'html',
    'class' => CountryColumn::class,
    'isAddLink' => false,
    'contentOptions' => [
        'class' => 'nowrap',
    ],
    'value' => function (Tariff $tariff) {
        $maxCount = 2;
        $tariffCountries = $tariff->tariffVoipCountries;
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
    },
];

$cityColumn = [
    'label' => Html::encode(Yii::t('models/' . TariffVoipCity::tableName(), 'city_id')),
    'attribute' => 'voip_city_id',
    'format' => 'html',
    'class' => CityColumn::class,
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

        $maxCount--;

        return sprintf(
            '%s<br/><abbr title="%s">… %d…</abbr>',
            implode('<br/>', array_slice($voipCities, 0, $maxCount)),
            implode(PHP_EOL, array_slice($voipCities, $maxCount)),
            $count - $maxCount
        );
    },
];

$voipCountryColumn = [
    'label' => 'Страны витрины',
    'attribute' => 'tariff_country_id',
    'format' => 'html',
    'class' => CountryColumn::class,
    'isAddLink' => false,
    'contentOptions' => [
        'class' => 'nowrap',
    ],
    'value' => function (Tariff $tariff) {
        $maxCount = 2;
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
    },
];


$ndcTypeColumn = [
    'label' => Html::encode(Yii::t('models/' . TariffVoipNdcType::tableName(), 'ndc_type_id')),
    'attribute' => 'voip_ndc_type_id',
    'format' => 'html',
    'class' => NdcTypeColumn::class,
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

        $maxCount--;

        return sprintf(
            '%s<br/><abbr title="%s">… %d…</abbr>',
            implode('<br/>', array_slice($voipNdcTypes, 0, $maxCount)),
            implode(PHP_EOL, array_slice($voipNdcTypes, $maxCount)),
            $count - $maxCount
        );
    },
];

// столбцы для конкретной услуги
switch ($serviceType->id) {

    case ServiceType::ID_VPBX:
        break;

    case ServiceType::ID_VOIP:
        $columns[] = $voipCountryColumn;
        $columns[] = $cityColumn;
        $columns[] = $ndcTypeColumn;
        break;

    case ServiceType::ID_VOIP_PACKAGE_CALLS:
        $columns[] = $voipCountryColumn;
        $columns[] = $cityColumn;
        $columns[] = $ndcTypeColumn;

        $columns[] = [
            'label' => Html::encode(Yii::t('models/' . Tariff::tableName(), 'voip_group_id')),
            'attribute' => 'voip_group_id',
            'class' => TariffVoipGroupColumn::class,
            'value' => function (Tariff $tariff) {
                return $tariff->voip_group_id;
            },
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
$resources = ResourceModel::findAll(['service_type_id' => $serviceType->id]);
foreach ($resources as $resource) {
    if (in_array($resource->id, [ResourceModel::ID_VOIP_PACKAGE_CALLS, ResourceModel::ID_VOIP_PACKAGE_CALLS])) {
        continue;
    }

    $columns[] = [
        'label' => Html::encode($resource->name . ($resource->unit ? ', ' . $resource->unit : '')),
        'value' => function (Tariff $tariff) use ($resource) {
            /** @var TariffResource $tariffResource */
            $tariffResources = $tariff->tariffResourcesIndexedByResourceId;

            if (!isset($tariffResources[$resource->id])) {
                return '';
            }

            $tariffResource = $tariffResources[$resource->id];

            if ($resource->isNumber()) {
                return strpos($tariffResource->amount, '.') !== false ?
                    sprintf('%.2f', $tariffResource->amount) :
                    $tariffResource->amount;
            }

            return $tariffResource->amount ? '+' : '-';
        },
    ];
}


$columns[] = [
    'class' => ActionColumn::class,
    'template' => '{delete}',
    'buttons' => [
        'delete' => function ($url, Tariff $model, $key) use ($baseView) {
            $params = array_merge(['id' => $model->id], $_GET);
            return $baseView->render('//layouts/_actionDrop', [
                'url' => '/uu/tariff/edit?' . http_build_query($params)
            ]);
        },
    ],
    'hAlign' => GridView::ALIGN_CENTER,
];

$dataProvider = $filterModel->search();

$isShowArchiveHtml = Html::beginTag('label') .
    'Показывать архивные тарифы: ' .
    Html::checkbox(
        $filterModel->formName() . '[is_show_archive]',
        $filterModel->is_show_archive,
        ['id' => 'is_show_archive']
    ) .
    Html::endTag('label');


$this->registerJs(
    new JsExpression(
        '$("body").on("click", "#is_show_archive", function() {
                    var name = "Form' . $filterModel->formName() . 'Data";
                    var data = Cookies.get(name);
                    data = data ? $.parseJSON(data) : {};
                    data["is_show_archive"] = $(this).is(\':checked\');
                    Cookies.set(name, data, { path: "/" });
                    $("#submitButtonFilter").click();
                });'
    )
);


echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'extraButtons' => $isShowArchiveHtml . $this->render('//layouts/_buttonCreate', ['url' => Tariff::getUrlNew($serviceType->id)]),
    'columns' => $columns,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);
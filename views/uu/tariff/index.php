<?php
/**
 * Список универсальных тарифов
 *
 * @var \yii\web\View $this
 * @var TariffFilter $filterModel
 */

use app\classes\grid\column\universal\CityColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\CurrencyColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\TariffPersonColumn;
use app\classes\grid\column\universal\TariffStatusColumn;
use app\classes\grid\column\universal\TariffVoipGroupColumn;
use app\classes\grid\column\universal\TariffVoipTarificateColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\classes\uu\filter\TariffFilter;
use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\Tariff;
use app\classes\uu\model\TariffResource;
use app\classes\uu\model\TariffVoipCity;
use app\modules\nnp\models\PackageMinute;
use app\modules\nnp\models\PackagePrice;
use app\modules\nnp\models\PackagePricelist;
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
        ['label' => $this->title = $serviceType->name, 'url' => Url::to(['uu/tariff', 'serviceTypeId' => $serviceType->id])],
    ],
]) ?>

<?php
// базовые столбцы
$columns = [
    [
        'attribute' => 'name',
        'class' => StringColumn::className(),
    ],
    [
        'label' => Yii::t('common', 'Price'),
        'value' => function (Tariff $tariff) {
            $tariffPeriods = $tariff->tariffPeriods;
            $tariffPeriod = reset($tariffPeriods);
            return sprintf('%d + %d', $tariffPeriod->price_min, $tariffPeriod->price_per_period);
        }
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
        }
    ],
    [
        'attribute' => 'tariff_person_id',
        'class' => TariffPersonColumn::className(),
    ],
];

// столбцы для конкретной услуги
switch ($serviceType->id) {

    case ServiceType::ID_VPBX:
        break;

    case ServiceType::ID_VOIP:
        $columns[] = [
            'label' => Html::encode(Yii::t('models/' . Tariff::tableName(), 'voip_tarificate_id')),
            'attribute' => 'voip_tarificate_id',
            'class' => TariffVoipTarificateColumn::className(),
            'value' => function (Tariff $tariff) {
                return $tariff->voip_tarificate_id;
            }
        ];

        $columns[] = [
            'label' => Html::encode(Yii::t('models/' . TariffVoipCity::tableName(), 'city_id')),
            'attribute' => 'voip_city_id',
            'format' => 'html',
            'class' => CityColumn::className(),
            'value' => function (Tariff $tariff) {
                return implode('<br>', $tariff->voipCities);
            }
        ];
        break;

    case ServiceType::ID_VOIP_PACKAGE:
        $columns[] = [
            'label' => Html::encode(Yii::t('models/' . TariffVoipCity::tableName(), 'city_id')),
            'attribute' => 'voip_city_id',
            'format' => 'html',
            'class' => CityColumn::className(),
            'value' => function (Tariff $tariff) {
                return implode('<br>', $tariff->voipCities);
            }
        ];

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
                $packageMinutes = $tariff->packageMinutes;
                $echoArray = array_map(function (PackageMinute $packageMinute) {
                    $destination = $packageMinute->destination;
                    return sprintf('%d %s', $packageMinute->minute, Html::a($destination->name, $destination->getUrl()));
                }, $packageMinutes);
                return implode('<br/>', $echoArray);
            }
        ];

        $columns[] = [
            'label' => 'Цена по направлениям',
            'format' => 'html',
            'value' => function (Tariff $tariff) {
                $packagePrices = $tariff->packagePrices;
                $echoArray = array_map(function (PackagePrice $packagePrice) {
                    $destination = $packagePrice->destination;
                    return sprintf('%d %s', $packagePrice->price, Html::a($destination->name, $destination->getUrl()));
                }, $packagePrices);
                return implode('<br/>', $echoArray);
            }
        ];

        $columns[] = [
            'label' => 'Прайслист с МГП',
            'format' => 'html',
            'value' => function (Tariff $tariff) {
                $packagePricelists = $tariff->packagePricelists;
                $echoArray = array_map(function (PackagePricelist $packagePricelist) {
                    $pricelist = $packagePricelist->pricelist;
                    return Html::a($pricelist->name, $pricelist->getUrl());
                }, $packagePricelists);
                return implode('<br/>', $echoArray);
            }
        ];
        break;
}

// столбцы с ресурсами
$resources = Resource::findAll(['service_type_id' => $serviceType->id]);
foreach ($resources as $resource) {
    $columns[] = [
        'label' => Html::encode($resource->name . ($resource->unit ? ', ' . $resource->unit : '')),
        'value' => function (Tariff $tariff) use ($resource) {
            /** @var TariffResource $tariffResource */
            $tariffResource = $tariff->getTariffResource($resource->id)->one();
            if ($resource->isNumber()) {
                return strpos($tariffResource->amount, '.') !== false ?
                    sprintf('%.2f', $tariffResource->amount) :
                    $tariffResource->amount;
            } else {
                return $tariffResource->amount ? '+' : '-';
            }
        }
    ];
}

// "действия"
$baseView = $this;
$columns[] = [
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
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => Tariff::getUrlNew($serviceType->id)]),
    'columns' => $columns,
]);
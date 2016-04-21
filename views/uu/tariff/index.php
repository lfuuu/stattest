<?php
/**
 * Список универсальных тарифов
 *
 * @var \yii\web\View $this
 * @var TariffFilter $filterModel
 */

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

    <p>
        <?= Html::a(
            Yii::t('common', 'Create'),
            Tariff::getUrlNew($serviceType->id),
            ['class' => 'btn btn-success glyphicon glyphicon-pencil']
        ) ?>
    </p>

<?php
// базовые столбцы
$columns = [
    [
        'attribute' => 'name',
        'class' => StringColumn::className(),
        'format' => 'html',
        'value' => function (Tariff $tariff) {
            return Html::a($tariff->name, $tariff->getUrl());
        }

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
        break;

    case ServiceType::ID_VOIP_PACKAGE:
        $columns[] = [
            'label' => Html::encode(Yii::t('models/' . Tariff::tableName(), 'voip_group_id')),
            'attribute' => 'voip_group_id',
            'class' => TariffVoipGroupColumn::className(),
            'value' => function (Tariff $tariff) {
                return $tariff->voip_group_id;
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

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
]);
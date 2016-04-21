<?php
/**
 * Список универсальных услуг
 *
 * @var \yii\web\View $this
 * @var AccountTariffFilter $filterModel
 */

use app\classes\grid\column\universal\CityColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\RegionColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\TariffPeriodColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\classes\uu\filter\AccountTariffFilter;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\ServiceType;

$serviceType = $filterModel->getServiceType();

// базовые столбцы
$columns = [
    [
        'label' => Yii::t('tariff', 'Universal services'),
        'attribute' => 'tariff_period_id',
        'class' => TariffPeriodColumn::className(),
        'serviceTypeId' => $serviceType->id,
        'format' => 'html',
        'value' => function (AccountTariff $accountTariff) {
            return Html::a(
                Html::encode($accountTariff->getName(false)),
                $accountTariff->getUrl()
            );
        }
    ],
    [
        'attribute' => 'client_account_id',
        'class' => IntegerColumn::className(),
        'format' => 'html',
        'value' => function (AccountTariff $accountTariff) {
            return Html::a(
                Html::encode($accountTariff->clientAccount->client),
                ['/client/view', 'id' => $accountTariff->client_account_id]
            );
        }
    ],
    [
        'attribute' => 'region_id',
        'class' => RegionColumn::className(),
    ],
];

// столбцы для конкретной услуги
switch ($serviceType->id) {

    case ServiceType::ID_VPBX:
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
        break;

    case ServiceType::ID_VOIP_PACKAGE:
        $columns[] = [
            'attribute' => 'city_id',
            'class' => CityColumn::className(),
        ];
        break;
}


echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
]) ?>
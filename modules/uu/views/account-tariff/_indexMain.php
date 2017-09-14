<?php
/**
 * Список универсальных услуг
 *
 * @var \app\classes\BaseView $this
 * @var AccountTariffFilter $filterModel
 */

use app\classes\grid\column\universal\BeautyLevelColumn;
use app\classes\grid\column\universal\CityColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\RegionColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\uu\column\TariffPeriodColumn;
use app\modules\uu\filter\AccountTariffFilter;
use app\modules\uu\models\AccountTariff;
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
        $columns[] = [
            'label' => 'Красивость',
            'attribute' => 'beauty_level',
            'class' => BeautyLevelColumn::className(),
            'value' => function (AccountTariff $accountTariff) {
                return $accountTariff->number->beauty_level;
            },
        ];
        break;

    case ServiceType::ID_VOIP_PACKAGE_CALLS:
        $columns[] = [
            'attribute' => 'city_id',
            'class' => CityColumn::className(),
        ];
        break;
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

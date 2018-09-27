<?php
/**
 * Расчет абонентки
 *
 * @var \app\classes\BaseView $this
 * @var AccountLogPeriodFilter $filterModel
 */

use app\classes\grid\column\universal\CityColumn;
use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\IsNullAndNotNullColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\commands\UbillerController;
use app\modules\uu\column\DatacenterColumn;
use app\modules\uu\column\InfrastructureLevelColumn;
use app\modules\uu\column\InfrastructureProjectColumn;
use app\modules\uu\column\ServiceTypeColumn;
use app\modules\uu\column\TariffPeriodColumn;
use app\modules\uu\filter\AccountLogPeriodFilter;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use app\widgets\GridViewExport\GridViewExport;
use yii\widgets\Breadcrumbs;

$accountLogPeriodTableName = AccountLogPeriod::tableName();
$accountTariffTableName = AccountTariff::tableName();
?>

<?= Breadcrumbs::widget([
    'links' => [
        [
            'label' => Yii::t('tariff', 'Universal tarifficator') .
                $this->render('//layouts/_helpConfluence', UbillerController::getHelpConfluence()),
            'encode' => false,
        ],

        ['label' => $this->title = Yii::t('tariff', 'Period tariffication'), 'url' => '/uu/account-log/period'],
        [
            'label' => $this->render('//layouts/_helpConfluence', AccountLogPeriod::getHelpConfluence()),
            'encode' => false,
        ],
    ],
]) ?>

<?php
$columns = [
    [
        'attribute' => 'id',
        'class' => IntegerColumn::class,
    ],
    [
        'attribute' => 'date_from',
        'class' => DateRangeDoubleColumn::class,
    ],
    [
        'attribute' => 'date_to',
        'class' => DateRangeDoubleColumn::class,
    ],
    [
        'attribute' => 'account_entry_id',
        'class' => IsNullAndNotNullColumn::class,
        'format' => 'html',
        'value' => function (AccountLogPeriod $accountLogPeriod) {
            $accountEntry = $accountLogPeriod->accountEntry;
            if (!$accountEntry) {
                return Yii::t('common', '(not set)');
            }

            return Html::a($accountEntry->date, $accountEntry->getUrl());
        },
    ],
    [
        'label' => 'Тип услуги',
        'attribute' => 'service_type_id',
        'class' => ServiceTypeColumn::class,
        'value' => function (AccountLogPeriod $accountLogPeriod) {
            return $accountLogPeriod->accountTariff->serviceType->name;
        },
    ],
    [
        'label' => Yii::t('models/' . $accountLogPeriodTableName, 'account_tariff_id'),
        'attribute' => 'tariff_period_id',
        'format' => 'html',
        'class' => TariffPeriodColumn::class,
        'serviceTypeId' => $filterModel->service_type_id,
        'value' => function (AccountLogPeriod $accountLogPeriod) {
            $accountTariff = $accountLogPeriod->accountTariff;
            return Html::a(
                Html::encode($accountLogPeriod->tariffPeriod->getName()), // $accountTariff->getName(false)
                $accountTariff->getUrl()
            );
        },
    ],
    [
        'label' => Yii::t('models/' . $accountTariffTableName, 'client_account_id'),
        'attribute' => 'client_account_id',
        'class' => IntegerColumn::class,
        'format' => 'html',
        'value' => function (AccountLogPeriod $accountLogPeriod) {
            return $accountLogPeriod->accountTariff->clientAccount->getLink();
        },
    ],
    [
        'attribute' => 'period_price',
        'class' => IntegerRangeColumn::class,
    ],
    [
        'attribute' => 'coefficient',
        'class' => IntegerRangeColumn::class,
    ],
    [
        'attribute' => 'price',
        'class' => IntegerRangeColumn::class,
    ],
];

// отображаемые колонки Итого в гриде
$summary = $filterModel->searchSummary();
$summaryColumns = [
    [
        'content' => Yii::t('common', 'Summary'),
        'options' => ['colspan' => 9],
    ],
    ['options' => ['class' => 'hidden']], // потому что colspan в первом столбце
    ['options' => ['class' => 'hidden']], // потому что colspan в первом столбце
    ['options' => ['class' => 'hidden']], // потому что colspan в первом столбце
    ['options' => ['class' => 'hidden']], // потому что colspan в первом столбце
    ['options' => ['class' => 'hidden']], // потому что colspan в первом столбце
    ['options' => ['class' => 'hidden']], // потому что colspan в первом столбце
    ['options' => ['class' => 'hidden']], // потому что colspan в первом столбце
    ['options' => ['class' => 'hidden']], // потому что colspan в первом столбце
    ['content' => $summary['account_log_period_price']],
];

if ($filterModel->service_type_id == ServiceType::ID_INFRASTRUCTURE) {
    $columns[] = [
        'label' => Yii::t('models/' . $accountTariffTableName, 'price'),
        'attribute' => 'account_tariff_price',
        'class' => IntegerRangeColumn::class,
        'value' => function (AccountLogPeriod $accountLogPeriod) {
            return $accountLogPeriod->accountTariff->price;
        },
    ];
    $summaryColumns[] = ['content' => $summary['account_tariff_price']];

    $columns[] = [
        'label' => Yii::t('models/' . $accountTariffTableName, 'infrastructure_project'),
        'attribute' => 'account_tariff_infrastructure_project',
        'class' => InfrastructureProjectColumn::class,
        'value' => function (AccountLogPeriod $accountLogPeriod) {
            return $accountLogPeriod->accountTariff->infrastructure_project;
        },
    ];
    $summaryColumns[] = [];

    $columns[] = [
        'label' => Yii::t('models/' . $accountTariffTableName, 'infrastructure_level'),
        'attribute' => 'account_tariff_infrastructure_level',
        'class' => InfrastructureLevelColumn::class,
        'value' => function (AccountLogPeriod $accountLogPeriod) {
            return $accountLogPeriod->accountTariff->infrastructure_level;
        },
    ];
    $summaryColumns[] = [];

    $columns[] = [
        'label' => Yii::t('models/' . $accountTariffTableName, 'datacenter_id'),
        'attribute' => 'account_tariff_datacenter_id',
        'class' => DatacenterColumn::class,
        'value' => function (AccountLogPeriod $accountLogPeriod) {
            return $accountLogPeriod->accountTariff->datacenter_id;
        },
    ];
    $summaryColumns[] = [];

    $columns[] = [
        'label' => Yii::t('models/' . $accountTariffTableName, 'city_id'),
        'attribute' => 'account_tariff_city_id',
        'class' => CityColumn::class,
        'value' => function (AccountLogPeriod $accountLogPeriod) {
            return $accountLogPeriod->accountTariff->city_id;
        },
    ];
    $summaryColumns[] = [];
}

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
    'afterHeader' => [ // итого
        [
            'options' => ['class' => \kartik\grid\GridView::TYPE_WARNING], // желтый фон
            'columns' => $summaryColumns,
        ]
    ],
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);

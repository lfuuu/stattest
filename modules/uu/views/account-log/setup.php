<?php
/**
 * Расчет платы за подключение
 *
 * @var \app\classes\BaseView $this
 * @var AccountLogSetupFilter $filterModel
 */

use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\IsNullAndNotNullColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\commands\UbillerController;
use app\modules\uu\column\ServiceTypeColumn;
use app\modules\uu\column\TariffPeriodColumn;
use app\modules\uu\filter\AccountLogSetupFilter;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountLogSetup;
use app\modules\uu\models\AccountTariff;
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

        ['label' => $this->title = Yii::t('tariff', 'Setup tariffication'), 'url' => '/uu/account-log/setup'],
        [
            'label' => $this->render('//layouts/_helpConfluence', AccountLogSetup::getHelpConfluence()),
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
        'attribute' => 'date',
        'class' => DateRangeDoubleColumn::class,
    ],
    [
        'label' => 'Тип услуги',
        'attribute' => 'service_type_id',
        'class' => ServiceTypeColumn::class,
        'value' => function (AccountLogSetup $accountLogSetup) {
            return $accountLogSetup->accountTariff->serviceType->name;
        }
    ],
    [
        'label' => Yii::t('models/' . $accountLogPeriodTableName, 'account_tariff_id'),
        'attribute' => 'tariff_period_id',
        'format' => 'html',
        'class' => TariffPeriodColumn::class,
        'serviceTypeId' => $filterModel->service_type_id,
        'value' => function (AccountLogSetup $accountLogSetup) {
            $accountTariff = $accountLogSetup->accountTariff;
            return Html::a(
                Html::encode($accountLogSetup->tariffPeriod->getName()), // $accountTariff->getName(false)
                $accountTariff->getUrl()
            );
        }
    ],
    [
        'label' => Yii::t('models/' . $accountTariffTableName, 'client_account_id'),
        'attribute' => 'client_account_id',
        'class' => IntegerColumn::class,
        'format' => 'html',
        'value' => function (AccountLogSetup $accountLogSetup) {
            return $accountLogSetup->accountTariff->clientAccount->getLink();
        }
    ],
    [
        'attribute' => 'price_setup',
        'class' => IntegerRangeColumn::class,
    ],
    [
        'attribute' => 'price_number',
        'class' => IntegerRangeColumn::class,
    ],
    [
        'attribute' => 'price',
        'class' => IntegerRangeColumn::class,
    ],
    [
        'attribute' => 'account_entry_id',
        'class' => IsNullAndNotNullColumn::class,
        'format' => 'html',
        'value' => function (AccountLogSetup $accountLogSetup) {
            $accountEntry = $accountLogSetup->accountEntry;
            if (!$accountEntry) {
                return Yii::t('common', '(not set)');
            }

            return Html::a($accountEntry->date, $accountEntry->getUrl());
        }
    ],
];

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);

<?php
/**
 * Расчет минималки
 *
 * @var \app\classes\BaseView $this
 * @var AccountLogMinFilter $filterModel
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
use app\modules\uu\filter\AccountLogMinFilter;
use app\modules\uu\models\AccountLogMin;
use app\modules\uu\models\AccountTariff;
use app\widgets\GridViewExport\GridViewExport;
use yii\widgets\Breadcrumbs;

$accountLogMinTableName = AccountLogMin::tableName();
$accountTariffTableName = AccountTariff::tableName();
?>

<?= Breadcrumbs::widget([
    'links' => [
        [
            'label' => Yii::t('tariff', 'Universal tarifficator') .
                $this->render('//layouts/_helpConfluence', UbillerController::getHelpConfluence()),
            'encode' => false,
        ],

        ['label' => $this->title = Yii::t('tariff', 'Min resource tariffication'), 'url' => '/uu/account-log/min'],
        [
            'label' => $this->render('//layouts/_helpConfluence', AccountLogMin::getHelpConfluence()),
            'encode' => false,
        ],
    ],
]) ?>

<?php
$columns = [
    [
        'attribute' => 'id',
        'class' => IntegerColumn::className(),
    ],
    [
        'attribute' => 'date_from',
        'class' => DateRangeDoubleColumn::className(),
    ],
    [
        'attribute' => 'date_to',
        'class' => DateRangeDoubleColumn::className(),
    ],
    [
        'label' => 'Тип услуги',
        'attribute' => 'service_type_id',
        'class' => ServiceTypeColumn::className(),
        'value' => function (AccountLogMin $accountLogMin) {
            return $accountLogMin->accountTariff->serviceType->name;
        }
    ],
    [
        'label' => Yii::t('models/' . $accountLogMinTableName, 'account_tariff_id'),
        'attribute' => 'tariff_period_id',
        'format' => 'html',
        'class' => TariffPeriodColumn::className(),
        'serviceTypeId' => $filterModel->service_type_id,
        'value' => function (AccountLogMin $accountLogMin) {
            $accountTariff = $accountLogMin->accountTariff;
            return Html::a(
                Html::encode($accountLogMin->tariffPeriod->getName()), // $accountTariff->getName(false)
                $accountTariff->getUrl()
            );
        }
    ],
    [
        'label' => Yii::t('models/' . $accountTariffTableName, 'client_account_id'),
        'attribute' => 'client_account_id',
        'class' => IntegerColumn::className(),
        'format' => 'html',
        'value' => function (AccountLogMin $accountLogMin) {
            return $accountLogMin->accountTariff->clientAccount->getLink();
        }
    ],
    [
        'attribute' => 'period_price',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'attribute' => 'coefficient',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'attribute' => 'price_with_coefficient',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'attribute' => 'price_resource',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'attribute' => 'price',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'attribute' => 'account_entry_id',
        'class' => IsNullAndNotNullColumn::className(),
        'format' => 'html',
        'value' => function (AccountLogMin $accountLogMin) {
            $accountEntry = $accountLogMin->accountEntry;
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

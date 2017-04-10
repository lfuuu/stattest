<?php
/**
 * Расчет абонентки
 *
 * @var \app\classes\BaseView $this
 * @var AccountLogPeriodFilter $filterModel
 */

use app\classes\grid\column\universal\DateRangeDoubleColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\IsNullAndNotNullColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\uu\column\ServiceTypeColumn;
use app\modules\uu\column\TariffPeriodColumn;
use app\modules\uu\filter\AccountLogPeriodFilter;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountTariff;
use yii\widgets\Breadcrumbs;

$accountLogPeriodTableName = AccountLogPeriod::tableName();
$accountTariffTableName = AccountTariff::tableName();
?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tarifficator'),
        ['label' => $this->title = Yii::t('tariff', 'Period tariffication'), 'url' => '/uu/account-log/period']
    ],
]) ?>

<?= GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => [
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
            'value' => function (AccountLogPeriod $accountLogPeriod) {
                return $accountLogPeriod->accountTariff->serviceType->name;
            }
        ],
        [
            'label' => Yii::t('models/' . $accountLogPeriodTableName, 'account_tariff_id'),
            'attribute' => 'tariff_period_id',
            'format' => 'html',
            'class' => TariffPeriodColumn::className(),
            'serviceTypeId' => $filterModel->service_type_id,
            'value' => function (AccountLogPeriod $accountLogPeriod) {
                $accountTariff = $accountLogPeriod->accountTariff;
                return Html::a(
                    Html::encode($accountLogPeriod->tariffPeriod->getName()), // $accountTariff->getName(false)
                    $accountTariff->getUrl()
                );
            }
        ],
        [
            'label' => Yii::t('models/' . $accountTariffTableName, 'client_account_id'),
            'attribute' => 'client_account_id',
            'class' => IntegerColumn::className(),
            'format' => 'html',
            'value' => function (AccountLogPeriod $accountLogPeriod) {
                return $accountLogPeriod->accountTariff->clientAccount->getLink();
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
            'attribute' => 'price',
            'class' => IntegerRangeColumn::className(),
        ],
        [
            'attribute' => 'account_entry_id',
            'class' => IsNullAndNotNullColumn::className(),
            'format' => 'html',
            'value' => function (AccountLogPeriod $accountLogPeriod) {
                $accountEntry = $accountLogPeriod->accountEntry;
                if (!$accountEntry) {
                    return Yii::t('common', '(not set)');
                }
                return Html::a($accountEntry->date, $accountEntry->getUrl());
            }
        ],
    ],
]) ?>

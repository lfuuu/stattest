<?php
/**
 * Расчет абонентки
 *
 * @var \yii\web\View $this
 * @var AccountLogPeriodFilter $filterModel
 */

use app\classes\grid\column\universal\DateRangeColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\TariffPeriodColumn;
use app\classes\Html;
use app\classes\uu\filter\AccountLogPeriodFilter;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountTariff;
use kartik\grid\GridView;
use yii\widgets\Breadcrumbs;

$accountLogPeriodTableName = AccountLogPeriod::tableName();
$accountTariffTableName = AccountTariff::tableName();
?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tarifficator'),
        ['label' => $this->title = Yii::t('tariff', 'Period tariffication'), 'url' => '/uu/accountlog/period']
    ],
]) ?>

<?= GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => [
        [
            'attribute' => 'date_from',
            'class' => DateRangeColumn::className(),
        ],
        [
            'attribute' => 'date_to',
            'class' => DateRangeColumn::className(),
        ],
//        [
//            'attribute' => 'tariff_period_id',
//            'format' => 'html',
//            'value' => function (AccountLogPeriod $accountLogPeriod) {
//                return Html::a(
//                    Html::encode($accountLogPeriod->tariffPeriod->getName()),
//                    $accountLogPeriod->tariffPeriod->getUrl()
//                );
//            }
//        ],
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
                return Html::a(
                    Html::encode($accountLogPeriod->accountTariff->clientAccount->client),
                    ['/client/view', 'id' => $accountLogPeriod->accountTariff->client_account_id]
                );
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
    ],
]) ?>

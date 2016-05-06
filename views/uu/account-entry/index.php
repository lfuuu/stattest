<?php
/**
 * Список проводок
 *
 * @var \yii\web\View $this
 * @var AccountEntryFilter $filterModel
 */

use app\classes\grid\column\universal\AccountEntryTypeColumn;
use app\classes\grid\column\universal\FloatRangeColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\MonthColumn;
use app\classes\grid\column\universal\ServiceTypeColumn;
use app\classes\grid\column\universal\TariffPeriodColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\classes\uu\filter\AccountEntryFilter;
use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountLogSetup;
use app\classes\uu\model\AccountTariff;
use yii\widgets\Breadcrumbs;

$accountTariffTableName = AccountTariff::tableName();
?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tarifficator'),
        ['label' => $this->title = Yii::t('tariff', 'Account entries'), 'url' => '/uu/account-entry']
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
            'attribute' => 'date',
            'class' => MonthColumn::className(),
            'value' => function (AccountEntry $accountEntry) {
                return Yii::$app->formatter->asDate($accountEntry->date, 'php:M Y');
            },
        ],
        [
            'label' => Yii::t('models/' . $accountTariffTableName, 'client_account_id'),
            'attribute' => 'client_account_id',
            'class' => IntegerColumn::className(),
            'format' => 'html',
            'value' => function (AccountEntry $accountEntry) {
                return $accountEntry->accountTariff->clientAccount->getLink();
            },
        ],
        [
            'label' => 'Тип услуги',
            'attribute' => 'service_type_id',
            'class' => ServiceTypeColumn::className(),
            'value' => function (AccountEntry $accountEntry) {
                return $accountEntry->accountTariff->serviceType->name;
            },
        ],
        [
            'attribute' => 'account_tariff_id',
            'class' => TariffPeriodColumn::className(),
            'format' => 'html',
            'serviceTypeId' => $filterModel->service_type_id,
            'value' => function (AccountEntry $accountEntry) {
                $accountTariff = $accountEntry->accountTariff;
                return Html::a(
                    Html::encode($accountTariff->getName(false)),
                    $accountTariff->getUrl()
                );
            },
        ],
        [
            'attribute' => 'type_id',
            'class' => AccountEntryTypeColumn::className(),
            'value' => function (AccountEntry $accountEntry) {
                return $accountEntry->getTypeName();
            },
        ],
        [
            'attribute' => 'price',
            'class' => FloatRangeColumn::className(),
            'format' => ['decimal', 'decimals' => 2],
        ],
        [
            'attribute' => 'price_without_vat',
            'class' => FloatRangeColumn::className(),
            'format' => ['decimal', 'decimals' => 2],
        ],
        [
            'attribute' => 'vat_rate',
            'class' => IntegerRangeColumn::className(),
            'format' => ['decimal', 'decimals' => 2],
        ],
        [
            'attribute' => 'vat',
            'class' => FloatRangeColumn::className(),
            'format' => ['decimal', 'decimals' => 2],
        ],
        [
            'attribute' => 'price_with_vat',
            'class' => FloatRangeColumn::className(),
            'format' => ['decimal', 'decimals' => 2],
        ],
        [
            'label' => 'Транзакции, у.е.',
            'format' => 'raw',
            'contentOptions' => [
                'class' => 'text-nowrap',
            ],
            'value' => function (AccountEntry $accountEntry) {

                switch ($accountEntry->type_id) {
                    case AccountEntry::TYPE_ID_SETUP:
                        $accountLogs = $accountEntry->accountLogSetups;
                        array_walk($accountLogs, function (&$accountLog) {
                            /** @var AccountLogSetup $accountLog */
                            $accountLog = sprintf('%s: <a href="%s">%.2f</a>',
                                Yii::$app->formatter->asDate($accountLog->date, 'php:j M'),
                                $accountLog->getUrl(),
                                $accountLog->price);
                        });
                        break;

                    case AccountEntry::TYPE_ID_PERIOD:
                        $accountLogs = $accountEntry->accountLogPeriods;
                        array_walk($accountLogs, function (&$accountLog) {
                            /** @var AccountLogPeriod $accountLog */
                            $accountLog = sprintf('%d-%s: <a href="%s">%.2f</a>',
                                Yii::$app->formatter->asDate($accountLog->date_from, 'php:j'),
                                Yii::$app->formatter->asDate($accountLog->date_to, 'php:j M'),
                                $accountLog->getUrl(),
                                $accountLog->price);
                        });
                        break;

                    default:
                        $accountLogs = $accountEntry->accountLogResources;
                        array_walk($accountLogs, function (&$accountLog) {
                            /** @var AccountLogResource $accountLog */
                            $accountLog = sprintf('%s: <a href="%s">%.2f</a>',
                                Yii::$app->formatter->asDate($accountLog->date, 'php:j M'),
                                $accountLog->getUrl(),
                                $accountLog->price);
                        });
                        break;
                }

                return implode('<br />', $accountLogs);

            },
        ],
    ],
]) ?>

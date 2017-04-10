<?php
/**
 * Список проводок
 *
 * @var \app\classes\BaseView $this
 * @var AccountEntryFilter $filterModel
 */

use app\classes\grid\column\universal\FloatRangeColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\IsNullAndNotNullColumn;
use app\classes\grid\column\universal\MonthColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\uu\column\AccountEntryTypeColumn;
use app\modules\uu\column\ServiceTypeColumn;
use app\modules\uu\column\TariffPeriodColumn;
use app\modules\uu\filter\AccountEntryFilter;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountLogMin;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountLogSetup;
use app\modules\uu\models\AccountTariff;
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
    'extraButtons' =>
        $this->render('/bill/_ico', ['clientAccountId' => $filterModel->client_account_id]) . ' ' .
        $this->render('/invoice/_ico', ['clientAccountId' => $filterModel->client_account_id]) . ' ' .
        $this->render('/balance/_ico', ['clientAccountId' => $filterModel->client_account_id]),
    'columns' => [
        [
            'attribute' => 'id',
            'class' => IntegerColumn::className(),
        ],
        [
            'attribute' => 'is_default',
            'class' => YesNoColumn::className(),
        ],
        [
            'attribute' => 'date',
            'class' => MonthColumn::className(),
            'value' => function (AccountEntry $accountEntry) {
                $format = $accountEntry->is_default ? 'LLL Y' : 'd LLL Y';
                return datefmt_format_object(new DateTime($accountEntry->date), $format, Yii::$app->formatter->locale); // нативный php date не поддерживает LLL/LLLL
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
            'label' => Yii::t('models/' . $accountTariffTableName, 'service_type_id'),
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
                return $accountEntry->getName();
            },
        ],
        [
            'attribute' => 'price',
            'class' => FloatRangeColumn::className(),
        ],
        [
            'attribute' => 'price_without_vat',
            'class' => FloatRangeColumn::className(),
        ],
        [
            'attribute' => 'vat_rate',
            'class' => IntegerRangeColumn::className(),
        ],
        [
            'attribute' => 'vat',
            'class' => FloatRangeColumn::className(),
        ],
        [
            'attribute' => 'price_with_vat',
            'class' => FloatRangeColumn::className(),
        ],
        [
            'label' => 'Транзакции, ¤',
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
                            $accountLog =
                                Yii::$app->formatter->asDate($accountLog->date, 'php:j M') . ': ' .
                                Html::a(
                                    sprintf('%.2f', $accountLog->price),
                                    $accountLog->getUrl()
                                );
                        });
                        break;

                    case AccountEntry::TYPE_ID_PERIOD:
                        $accountLogs = $accountEntry->accountLogPeriods;
                        array_walk($accountLogs, function (&$accountLog) {
                            /** @var AccountLogPeriod $accountLog */
                            $accountLog =
                                Yii::$app->formatter->asDate($accountLog->date_from, 'php:j') . '-' .
                                Yii::$app->formatter->asDate($accountLog->date_to, 'php:j M') . ': ' .
                                Html::a(
                                    sprintf('%.2f', $accountLog->price),
                                    $accountLog->getUrl()
                                );
                        });
                        break;

                    case AccountEntry::TYPE_ID_MIN:
                        $accountLogs = $accountEntry->accountLogMins;
                        array_walk($accountLogs, function (&$accountLog) {
                            /** @var AccountLogMin $accountLog */
                            $accountLog =
                                Yii::$app->formatter->asDate($accountLog->date_from, 'php:j') . '-' .
                                Yii::$app->formatter->asDate($accountLog->date_to, 'php:j M') . ': ' .
                                Html::a(
                                    sprintf('%.2f', $accountLog->price),
                                    $accountLog->getUrl()
                                );
                        });
                        break;

                    default:
                        $accountLogs = $accountEntry->accountLogResources;
                        array_walk($accountLogs, function (&$accountLog) {
                            /** @var AccountLogResource $accountLog */
                            $accountLog = Yii::$app->formatter->asDate($accountLog->date, 'php:j M') . ': ' .
                                Html::a(
                                    sprintf('%.2f', $accountLog->price),
                                    $accountLog->getUrl()
                                );
                        });
                        break;
                }

                return implode('<br />', $accountLogs);

            },
        ],
        [
            'attribute' => 'bill_id',
            'class' => IsNullAndNotNullColumn::className(),
            'format' => 'html',
            'value' => function (AccountEntry $accountEntry) {
                $bill = $accountEntry->bill;
                if (!$bill) {
                    return Yii::t('common', '(not set)');
                }
                return Html::a($bill->date, $bill->getUrl());
            }
        ],
    ],
]) ?>

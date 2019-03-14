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
use app\classes\grid\column\universal\MonthColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\commands\UbillerController;
use app\models\Language;
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
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use app\widgets\GridViewExport\GridViewExport;
use kartik\widgets\Select2;
use yii\widgets\Breadcrumbs;

$accountTariffTableName = AccountTariff::tableName();

$tariffTableName = Tariff::tableName();
$serviceTypeTableName = ServiceType::tableName();

$tariffs = AccountTariff::find()
    ->select(['CONCAT(' . $serviceTypeTableName . '.name' . ',' . '". "' . ',' . $tariffTableName . '.name' . ',' . '"."' . ') as name', $tariffTableName . '.id'])
    ->where(['client_account_id' => $filterModel->client_account_id])
    ->andWhere(['not', [$tariffTableName . '.name' => null]])
    ->joinWith('serviceType')
    ->joinWith('accountTariffLogs.tariffPeriod.tariff')
    ->indexBy('id')
    ->orderBy(['name' => SORT_ASC])
    ->groupBy($serviceTypeTableName . '.name')
    ->asArray()
    ->column();

?>

<?= Breadcrumbs::widget([
    'links' => [
        [
            'label' => Yii::t('tariff', 'Universal tarifficator') .
                $this->render('//layouts/_helpConfluence', UbillerController::getHelpConfluence()),
            'encode' => false,
        ],

        ['label' => $this->title = Yii::t('tariff', 'Account entries'), 'url' => '/uu/account-entry'],
        [
            'label' => $this->render('//layouts/_helpConfluence', AccountEntry::getHelpConfluence()),
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
        'attribute' => 'is_next_month',
        'class' => YesNoColumn::class,
    ],
    [
        'attribute' => 'date',
        'class' => MonthColumn::class,
        'value' => function (AccountEntry $accountEntry) {
            return datefmt_format_object(new DateTime($accountEntry->date), 'LLL Y', Yii::$app->formatter->locale); // нативный php date не поддерживает LLL/LLLL
        },
    ],
    [
        'label' => Yii::t('models/' . $accountTariffTableName, 'client_account_id'),
        'attribute' => 'client_account_id',
        'class' => IntegerColumn::class,
        'format' => 'html',
        'value' => function (AccountEntry $accountEntry) {
            return $accountEntry->accountTariff->clientAccount->getLink();
        },
    ],
    [
        'label' => Yii::t('models/' . $accountTariffTableName, 'service_type_id'),
        'attribute' => 'service_type_id',
        'class' => ServiceTypeColumn::class,
        'value' => function (AccountEntry $accountEntry) {
            return $accountEntry->accountTariff->serviceType->name;
        },
    ],
    [
        'attribute' => 'account_tariff_id',
        'class' => TariffPeriodColumn::class,
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
        'label' => 'Тариф',
        'value' => function (AccountEntry $accountEntry) {
            return $accountEntry->accountTariff->tariffPeriod->tariff->name;
        },
        'filter' => Select2::widget([
            'model' => $filterModel,
            'attribute' => 'tariff_id',
            'data' => $tariffs,
            'value' => $filterModel->tariff_id,
            'options' => [
                'placeholder' => 'Выберите тариф'
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])
    ],
    [
        'attribute' => 'type_id',
        'class' => AccountEntryTypeColumn::class,
        'value' => function (AccountEntry $accountEntry) {
            return $accountEntry->getName(Language::LANGUAGE_DEFAULT, $isFullDocument = false);
        },
    ],
    [
        'attribute' => 'price',
        'class' => FloatRangeColumn::class,
    ],
    [
        'attribute' => 'cost_price',
        'class' => FloatRangeColumn::class,
    ],
    [
        'attribute' => 'price_without_vat',
        'class' => FloatRangeColumn::class,
    ],
    [
        'attribute' => 'vat_rate',
        'class' => IntegerRangeColumn::class,
    ],
    [
        'attribute' => 'vat',
        'class' => FloatRangeColumn::class,
    ],
    [
        'attribute' => 'price_with_vat',
        'class' => FloatRangeColumn::class,
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
                    break;

                case AccountEntry::TYPE_ID_PERIOD:
                    $accountLogs = $accountEntry->accountLogPeriods;
                    break;

                case AccountEntry::TYPE_ID_MIN:
                    $accountLogs = $accountEntry->accountLogMins;
                    break;

                default:
                    $accountLogs = $accountEntry->accountLogResources;
                    break;
            }

            $htmlArray = [];
            foreach ($accountLogs as $accountLog) {
                /** @var AccountLogPeriod|AccountLogResource|AccountLogMin|AccountLogSetup $accountLog */
                $htmlArray[] = (
                    ($accountEntry->type_id == AccountEntry::TYPE_ID_SETUP) ?
                        Yii::$app->formatter->asDate($accountLog->date, 'php:j M') . ': ' :

                        ($accountLog->date_from == $accountLog->date_to ? '' : Yii::$app->formatter->asDate($accountLog->date_from, 'php:j') . '-') .
                        Yii::$app->formatter->asDate($accountLog->date_to, 'php:j M') . ': '
                    ) .
                    Html::a(sprintf('%.2f', $accountLog->price), $accountLog->getUrl());
            }

            return implode('<br />', $htmlArray);

        },
    ],
    [
        'attribute' => 'bill_id',
        'class' => IntegerColumn::class,
        'format' => 'html',
        'value' => function (AccountEntry $accountEntry) {
            $bill = $accountEntry->bill;
            if (!$bill) {
                return Yii::t('common', '(not set)');
            }

            return Html::a($bill->date, $bill->getUrl());
        }
    ],
];

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'extraButtons' =>
        $this->render('/bill/_ico', ['clientAccountId' => $filterModel->client_account_id]) . ' ' .
        $this->render('/invoice/_ico', ['clientAccountId' => $filterModel->client_account_id]) . ' ' .
        $this->render('/balance/_ico', ['clientAccountId' => $filterModel->client_account_id]),
    'columns' => $columns,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);

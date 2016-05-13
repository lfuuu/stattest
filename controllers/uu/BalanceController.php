<?php
/**
 * Бухгалтерский баланс
 */

namespace app\controllers\uu;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountLogPeriod;
use app\classes\uu\model\AccountLogResource;
use app\classes\uu\model\AccountLogSetup;
use app\classes\uu\model\AccountTariff;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\Payment;
use Yii;
use yii\filters\AccessControl;

class BalanceController extends BaseController
{
    // Вернуть текущего клиента, если он есть
    use AddClientAccountFilterTraits;

    /**
     * Права доступа
     * @return []
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'roles' => ['newaccounts_balance.read'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionView($clientAccountId = null)
    {
        // Вернуть текущего клиента, если он есть
        !$clientAccountId && $clientAccountId = $this->getCurrentClientAccountId();

        if ($clientAccountId) {
            $accountTariffTableName = AccountTariff::tableName();

            // клиент
            $clientAccount = ClientAccount::findOne($clientAccountId);
            $currency = $clientAccount->currencyModel;

            // сводная информация AccountLogSetup
            $accountLogSetupTableName = AccountLogSetup::tableName();
            $accountLogSetupSummary = AccountLogSetup::find()
                ->select([
                    'total_count' => 'COUNT(*)',
                    'total_price' => 'SUM(' . $accountLogSetupTableName . '.price)',
                    'account_tariff_id' => $accountLogSetupTableName . '.account_tariff_id', // потому что джойнится по нему, а yii зачем то лезет в результирующий массив
                ])
                ->joinWith('accountTariff')
                ->where([$accountTariffTableName . '.client_account_id' => $clientAccountId])
                ->asArray()
                ->one();

            // сводная информация AccountLogPeriod
            $accountLogPeriodTableName = AccountLogPeriod::tableName();
            $accountLogPeriodSummary = AccountLogPeriod::find()
                ->select([
                    'total_count' => 'COUNT(*)',
                    'total_price' => 'SUM(' . $accountLogPeriodTableName . '.price)',
                    'account_tariff_id' => $accountLogPeriodTableName . '.account_tariff_id', // потому что джойнится по нему, а yii зачем то лезет в результирующий массив
                ])
                ->joinWith('accountTariff')
                ->where([$accountTariffTableName . '.client_account_id' => $clientAccountId])
                ->asArray()
                ->one();

            // сводная информация AccountLogResource
            $accountLogResourceTableName = AccountLogResource::tableName();
            $accountLogResourceSummary = AccountLogResource::find()
                ->select([
                    'total_count' => 'COUNT(*)',
                    'total_price' => 'SUM(' . $accountLogResourceTableName . '.price)',
                    'account_tariff_id' => $accountLogResourceTableName . '.account_tariff_id', // потому что джойнится по нему, а yii зачем то лезет в результирующий массив
                ])
                ->joinWith('accountTariff')
                ->where([$accountTariffTableName . '.client_account_id' => $clientAccountId])
                ->asArray()
                ->one();

            // сводная информация AccountEntry
            $accountEntryTableName = AccountEntry::tableName();
            $accountEntrySummary = AccountEntry::find()
                ->select([
                    'total_count' => 'COUNT(*)',
                    'total_price' => 'SUM(' . $accountEntryTableName . '.price)',
                    'account_tariff_id' => $accountEntryTableName . '.account_tariff_id', // потому что джойнится по нему, а yii зачем то лезет в результирующий массив
                ])
                ->joinWith('accountTariff')
                ->where([$accountTariffTableName . '.client_account_id' => $clientAccountId])
                ->andWhere(['<', $accountEntryTableName . '.date', date('Y-m-01')])// кроме этого месяца
                ->asArray()
                ->one();

            // сводная информация Payment
            $paymentSummary = Payment::find()
                ->select([
                    'total_count' => 'COUNT(*)',
                    'total_price' => 'SUM(sum)',
                ])
                ->where(['client_id' => $clientAccountId])
                ->asArray()
                ->one();

            // Все проводки клиента для грида
            $accountEntryTableName = AccountEntry::tableName();
            $accountEntries = AccountEntry::find()
                ->joinWith('accountTariff')
                ->where([$accountTariffTableName . '.client_account_id' => $clientAccountId])
//                ->andWhere(['>', $accountEntryTableName . '.price', 0])
                ->orderBy([
                    'date' => SORT_DESC,
                    'account_tariff_id' => SORT_ASC,
                    'type_id' => SORT_ASC,
                ])
                ->all();

            // Все платежи клиента для грида
            $payments = Payment::find()
                ->where(['client_id' => $clientAccountId])
                ->orderBy([
                    'payment_date' => SORT_DESC,
                    'id' => SORT_DESC,
                ])
                ->all();

            // Все старые счета клиента для грида
            $bills = Bill::find()
                ->where(['client_id' => $clientAccountId])
                ->orderBy([
                    'bill_date' => SORT_DESC,
                    'id' => SORT_DESC,
                ])
                ->all();

        } else {
            $clientAccount =
            $currency =
            $accountEntries =
            $payments =
            $bills =
            $accountEntrySummary =
            $accountLogSetupSummary =
            $accountLogPeriodSummary =
            $accountLogResourceSummary =
            $paymentSummary =
                null;
        }

        return $this->render('view', [
            'clientAccount' => $clientAccount,
            'currency' => $currency,
            'accountEntries' => $accountEntries,
            'payments' => $payments,
            'bills' => $bills,
            'accountEntrySummary' => $accountEntrySummary,
            'accountLogSetupSummary' => $accountLogSetupSummary,
            'accountLogPeriodSummary' => $accountLogPeriodSummary,
            'accountLogResourceSummary' => $accountLogResourceSummary,
            'paymentSummary' => $paymentSummary,
        ]);
    }
}
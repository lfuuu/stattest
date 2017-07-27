<?php
/**
 * Бухгалтерский баланс
 */

namespace app\modules\uu\controllers;

use app\classes\BaseController;
use app\classes\traits\AddClientAccountFilterTraits;
use app\models\Bill;
use app\models\ClientAccount;
use app\models\Payment;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountTariff;
use yii\filters\AccessControl;

class BalanceController extends BaseController
{
    // Вернуть текущего клиента, если он есть
    use AddClientAccountFilterTraits;

    /**
     * Права доступа
     *
     * @return array
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
     * @param int $clientAccountId
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionView($clientAccountId = null)
    {
        // Вернуть текущего клиента, если он есть
        !$clientAccountId && $clientAccountId = $this->_getCurrentClientAccountId();

        if ($clientAccountId) {
            $accountTariffTableName = AccountTariff::tableName();

            // клиент
            $clientAccount = ClientAccount::findOne($clientAccountId);
            $currency = $clientAccount->currencyModel;

            // сводная информация AccountEntry
            $accountEntryTableName = AccountEntry::tableName();
            $accountEntrySummary = AccountEntry::find()
                ->select(
                    [
                        'total_count' => 'COUNT(*)',
                        'total_price' => 'SUM(' . $accountEntryTableName . '.price)',
                        'account_tariff_id' => $accountEntryTableName . '.account_tariff_id', // потому что джойнится по нему, а yii зачем то лезет в результирующий массив
                    ]
                )
                ->joinWith('accountTariff')
                ->where([$accountTariffTableName . '.client_account_id' => $clientAccountId])
                ->andWhere(['<', $accountEntryTableName . '.date', date('Y-m-01')])// кроме этого месяца
                ->asArray()
                ->one();

            // сводная информация Payment
            $paymentSummary = Payment::find()
                ->select(
                    [
                        'total_count' => 'COUNT(*)',
                        'total_price' => 'SUM(sum)',
                    ]
                )
                ->where(['client_id' => $clientAccountId])
                ->asArray()
                ->one();

            // Все универсальные счета клиента
            $uuBills = \app\modules\uu\models\Bill::find()
                ->where(['client_account_id' => $clientAccountId])
                ->orderBy(
                    [
                        'date' => SORT_DESC,
                        'id' => SORT_DESC,
                    ]
                )
                ->all();

            $uuBillSummary = \app\modules\uu\models\Bill::find()
                ->select(
                    [
                        'total_price' => 'SUM(price)',
                    ]
                )
                ->where(['client_account_id' => $clientAccountId])
                ->asArray()
                ->one();

            // Все платежи клиента для грида
            $payments = Payment::find()
                ->where(['client_id' => $clientAccountId])
                ->orderBy(
                    [
                        'payment_date' => SORT_DESC,
                        'id' => SORT_DESC,
                    ]
                )
                ->all();

            // Все старые счета клиента
            $billsUsage = Bill::find()
                ->where(
                    [
                        'client_id' => $clientAccountId,
                        'biller_version' => ClientAccount::VERSION_BILLER_USAGE
                    ]
                )
                ->orderBy(
                    [
                        'bill_date' => SORT_DESC,
                        'id' => SORT_DESC,
                    ]
                )
                ->all();

            // Все сконвертиованные новые счета в старые счета клиента
            $billsUniversal = Bill::find()
                ->where(
                    [
                        'client_id' => $clientAccountId,
                        'biller_version' => ClientAccount::VERSION_BILLER_UNIVERSAL
                    ]
                )
                ->orderBy(
                    [
                        'bill_date' => SORT_DESC,
                        'id' => SORT_DESC,
                    ]
                )
                ->all();


        } else {
            $clientAccount
                = $currency
                = $payments
                = $uuBills
                = $billsUsage
                = $billsUniversal
                = $accountEntrySummary
                = $paymentSummary
                = $uuBillSummary
                = null;
        }

        return $this->render(
            'view',
            [
                'clientAccount' => $clientAccount,
                'currency' => $currency,
                'payments' => $payments,
                'uuBills' => $uuBills,
                'billsUsage' => $billsUsage,
                'billsUniversal' => $billsUniversal,
                'accountEntrySummary' => $accountEntrySummary,
                'paymentSummary' => $paymentSummary,
                'uuBillSummary' => $uuBillSummary,
            ]
        );
    }
}
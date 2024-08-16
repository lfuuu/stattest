<?php

namespace app\controllers\api\internal;

use app\classes\ActOfReconciliation;
use app\exceptions\web\NotImplementedHttpException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\Country;
use app\modules\uu\models\Bill as uuBill;
use app\modules\uu\models\ResourceModel;
use app\classes\ApiInternalController;

class AccountingController extends ApiInternalController
{
    /**
     * @throws NotImplementedHttpException
     */
    public function actionIndex()
    {
        throw new NotImplementedHttpException;
    }

    /**
     * @SWG\Get(tags={"Accounting"}, path="/internal/accounting/get-current-statement/", summary="Получение текущей выписки", operationId="getCurrentStatement",
     *   @SWG\Parameter(name="accountId", type="integer", description="ID ЛС", in = "query", default=""),
     *
     *   @SWG\Response(response=200, description="выписка",
     *   ),
     * )
     */
    public function actionGetCurrentStatement($accountId)
    {
        $lines = [];
        $sum = 0;

        $account = ClientAccount::findOne(['id' => $accountId]);

        if (!$account) {
            throw new \InvalidArgumentException('Account not found');
        }

        $query = uuBill::getUnconvertedAccountEntries($accountId)->with('tariffPeriod.tariff');

        foreach ($query->each() as $uuLine) {
            $lines[] = [
                'item' => $uuLine->getFullName(),
                'service_type' => $uuLine->tariffPeriod->tariff->serviceType->getAttributes(['id', 'name']),
                'type_id' => $uuLine->type_id,
                'sum' => (float)number_format($uuLine->price_with_vat, 2, '.', '')
            ];

            $sum += $uuLine['price_with_vat'];
        }

        $balance = $account->billingCounters->realtimeBalance;
        $accountingBalance = $account->balance;


        $diffBalance = $accountingBalance - $balance;

        $sum += $diffBalance;

        if ($diffBalance) {
            $lines[] = [
                'item' => \Yii::t('models/' . ResourceModel::tableName(), 'Resource #' . ResourceModel::ID_RESOURCES_WITHOUT_ENTRY, [], $account->clientContractModel->clientContragent->lang_code),
                'sum' => (float)number_format($diffBalance, 2, '.', ''),
            ];
        }


        return [
            'account_id' => $accountId,
            "bill" => [
                "bill_no" => 'current_statement',
                "is_rollback" => 0,
                "is_1c" => 0,
                "lines" => $lines,
                "sum_total" => (float)number_format($sum, 2, '.', ''),
                "dtypes" => ['bill_no' => 'current_statement', 'ts' => time()]
            ],
            "link" => [
            ],
        ];
    }

    /**
     * @SWG\Get(tags={"Accounting"}, path="/internal/accounting/get-invoice-balance/", summary="Получение баланса по с/ф", operationId="getInvoiceBalance",
     *   @SWG\Parameter(name="accountId", type="integer", description="ID ЛС", in = "query", default=""),
     *   @SWG\Parameter(name="countryCode", type="integer", description="Код страны (Россия - 643, Венгрия - 348), если не установлена - берется из точки подключения, или организциии клиента", in = "query", default=""),
     *
     *   @SWG\Response(response=200, description="баланс по с/ф",
     *   ),
     * )
     */
    public function actionGetInvoiceBalance($accountId, $countryCode = null)
    {
        if ($countryCode && !($country = Country::findOne(['code' => $countryCode]))) {
            throw new \InvalidArgumentException("country_code_is_bad");
        }

        if (is_array($accountId) || !$accountId || !preg_match("/^\d{1,6}$/", $accountId)) {
            throw new \InvalidArgumentException("account_is_bad");
        }

        $clientAccount = ClientAccount::findOne(['id' => $accountId]);
        if (!$clientAccount) {
            throw new \InvalidArgumentException("account_not_found");
        }

        return ActOfReconciliation::me()->getData(
            $clientAccount,
            null,
            (new \DateTimeImmutable('now'))
                ->modify('last day of this month')
                ->format(DateTimeZoneHelper::DATE_FORMAT)
            , true, true, ($country ? $country->code : null)
        );
    }


}
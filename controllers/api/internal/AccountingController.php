<?php

namespace app\controllers\api\internal;

use app\exceptions\web\NotImplementedHttpException;
use app\models\ClientAccount;
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
}
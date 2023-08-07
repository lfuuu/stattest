<?php

namespace app\controllers\api\internal;

use app\exceptions\web\NotImplementedHttpException;
use app\modules\uu\models\Bill as uuBill;
use Yii;
use app\classes\Assert;
use app\classes\ApiInternalController;
use app\models\billing\Trunk;
use app\models\billing\Server;

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

        $query = uuBill::getUnconvertedAccountEntries($accountId)->with('tariffPeriod.tariff');

        foreach ($query->each() as $uuLine) {
            $lines[] = [
                'item' => $uuLine->getFullName(),
                'service_type' => $uuLine->tariffPeriod->tariff->serviceType->getAttributes(['id', 'name']),
                'type_id' => $uuLine->type_id,
                'date_from' => '',
                'amount' => 1,
                'price' => number_format($uuLine->price_with_vat, 2, '.', ''),
                'sum' => number_format($uuLine->price_with_vat, 2, '.', '')
            ];

            $sum += $uuLine['price_with_vat'];
        }

        return [
            'account_id' => $accountId,
            "bill" => [
                "bill_no" => 'current_statement',
                "is_rollback" => 0,
                "is_1c" => 0,
                "lines" => $lines,
                "sum_total" => $sum,
                "dtypes" => ['bill_no' => 'current_statement', 'ts' => time()]
            ],
            "link" => [
            ],
        ];
    }
}
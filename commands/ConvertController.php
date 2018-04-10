<?php

namespace app\commands;

use app\models\Bill;
use app\models\ClientAccount;
use yii\console\Controller;

class ConvertController extends Controller
{
    /**
     * Конвертация упрощенных счетов.
     */
    public function actionIndex()
    {
        $clientAccountQuery = ClientAccount::find()
            ->where([
                'type_of_bill' => ClientAccount::TYPE_OF_BILL_SIMPLE,
            ]);
        foreach ($clientAccountQuery->each() as $clientAccount) {
            echo PHP_EOL . 'clientAccountId: ' . $clientAccount->id;
            $billQuery = Bill::find()->where([
                'client_id' => $clientAccount->id
            ]);
            foreach ($billQuery->each() as $bill) {
                if (strpos($bill->bill_no, '/') !== false) {
                    continue;
                }
                echo PHP_EOL . 'bill: ' . $bill->bill_no;
                Bill::dao()->recalcBill($bill);
            }

            ClientAccount::dao()->updateBalance($clientAccount->id);
        }
    }
}

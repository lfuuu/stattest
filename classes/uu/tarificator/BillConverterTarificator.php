<?php

namespace app\classes\uu\tarificator;

use app\classes\uu\model\Bill;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;

/**
 * Конвертацию УУ-счетов в старую бухгалтерию
 */
class BillConverterTarificator implements TarificatorI
{
    /**
     * @param int|null $accountClientId Если указан, то только для этого аккаунта. Если не указан - для всех
     */
    public function tarificate($accountClientId = null)
    {
        $billTableName = Bill::tableName();
        $clientAccountTableName = ClientAccount::tableName();

        $activeQuery = Bill::find()
            ->joinWith('clientAccount')
            //
            // которые требуют конвертации (не сконвертированы или изменились после конвертирования)
            ->where([$billTableName . '.is_converted' => 0])
            //
            // либо на доплату, либо за прошлый месяц
            ->andWhere('(' . $billTableName . '.is_default = 0 OR ' . $billTableName . '.date = :date)', [
                ':date' => (new \DateTimeImmutable())
                    ->modify('first day of previous month')// @todo таймзона клиента
                    ->format(DateTimeZoneHelper::DATE_FORMAT),
            ])
            //
            // только предоплата
            ->andWhere([$clientAccountTableName . '.is_postpaid' => 0])
            //
            // только УУ
            ->andWhere([$clientAccountTableName . '.account_version' => ClientAccount::VERSION_BILLER_UNIVERSAL]);;

        if ($accountClientId) {
            // только конкретный аккаунт
            $activeQuery->andWhere([$billTableName . '.client_account_id' => $accountClientId]);
        }

        /** @var Bill $bill */
        foreach ($activeQuery->each() as $bill) {
            \app\models\Bill::dao()->transferUniversalBillsToBills($bill);
            echo '. ';
        }

    }
}

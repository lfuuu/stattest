<?php

namespace app\modules\uu\tarificator;

use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\Bill;

/**
 * Конвертацию УУ-счетов в старую бухгалтерию
 */
class BillConverterTarificator extends Tarificator
{
    /**
     * @param int|null $accountClientId Если указан, то только для этого ЛС. Если не указан - для всех
     */
    public function tarificate($accountClientId = null)
    {
        $activeQuery = Bill::find()
            //
            // которые требуют конвертации (не сконвертированы или изменились после конвертирования)
            ->where(['is_converted' => 0])
            //
            // и либо на доплату, либо за прошлый месяц
            ->andWhere('(is_default = 0 OR date <= :date)', [
                ':date' => (new \DateTimeImmutable())
                    ->modify('first day of this month')// @todo таймзона клиента
                    ->format(DateTimeZoneHelper::DATE_FORMAT),
            ]);

        if ($accountClientId) {
            // только конкретный ЛС
            $activeQuery->andWhere(['client_account_id' => $accountClientId]);
        }

        /** @var Bill $bill */
        foreach ($activeQuery->each() as $bill) {
            \app\models\Bill::dao()->transferUniversalBillsToBills($bill);
            $this->out('. ');
        }

    }
}

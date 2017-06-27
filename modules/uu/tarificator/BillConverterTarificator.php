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
     * @throws \Exception
     */
    public function tarificate($accountClientId = null)
    {
        $activeQuery = Bill::find()
            ->where(['is_converted' => 0]) // которые не сконвертированы или изменились после конвертирования
            ->andWhere(['>', 'price', 0]) // и цена больше нуля
            ->andWhere([ // и за прошлый месяц
                '<=',
                'date',
                (new \DateTimeImmutable())
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

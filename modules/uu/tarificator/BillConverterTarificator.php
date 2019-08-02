<?php

namespace app\modules\uu\tarificator;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\Bill;
use app\widgets\ConsoleProgress;

/**
 * Конвертацию УУ-счетов в старую бухгалтерию
 */
class BillConverterTarificator extends Tarificator
{
    /**
     * @param int|null $clientAccountId Если указан, то только для этого ЛС. Если не указан - для всех
     * @throws ModelValidationException
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function tarificate($clientAccountId = null)
    {
        $activeQuery = Bill::find()
            ->where(['is_converted' => 0])// которые не сконвертированы или изменились после конвертирования
            ->andWhere([ // и за прошлый месяц
                '<=',
                'date',
                (new \DateTimeImmutable())
                    ->modify('first day of this month')// @todo таймзона клиента
                    ->format(DateTimeZoneHelper::DATE_FORMAT),
            ]);

        if ($clientAccountId) {
            // только конкретный ЛС
            $activeQuery->andWhere(['client_account_id' => $clientAccountId]);
        }

        $progress = new ConsoleProgress($activeQuery->count(), function ($string) {
            $this->out($string);
        });
        foreach ($activeQuery->each() as $bill) {
            $progress->nextStep();
            /** @var Bill $bill */
            $this->transferBill($bill);
        }
    }

    /**
     * Перенос универсального счёта в new_bills
     *
     * @param Bill $bill
     * @throws ModelValidationException
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function transferBill(Bill $bill)
    {
        \app\models\Bill::dao()->transferUniversalBillsToBills($bill);

        $bill->is_converted = 1;
        if (!$bill->save()) {
            throw new ModelValidationException($bill);
        }
    }
}

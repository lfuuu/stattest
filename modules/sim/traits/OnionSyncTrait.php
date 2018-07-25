<?php

namespace app\modules\sim\traits;

use InvalidArgumentException;

trait OnionSyncTrait
{
    /**
     * Алгоритм "Луковичной синхронизация"
     *
     * Локальная синхронизация с postgres и mysql будут исполняться в рамках транзакции.
     * Коммит транзакций будет происходить в том случае, если была успешно произведена удаленная синхронизация.
     * В противном случае произойдет откат транзакции, ошибка удаленной синхронизации будет залогированна.
     *
     * @param \yii\db\Transaction[] $transactions
     * @return array
     */
    public function callOnionSync($transactions = [])
    {
        if (!$transactions)
            throw new InvalidArgumentException('Отсутствуют транзакции');

        try {
            // Локальная синхронизацию с Postgres и Mysql
            $this->_localSyncOperation();
            // Удаленная синхронизация с MVNO
            $this->_globalSyncOperation();

            // Выполнение коммитов транзакций
            foreach ($transactions as $transaction) {
                $transaction->commit();
            }
        } catch (\Exception $e) {
            // Отмена коммитов транзакций
            foreach ($transactions as $transaction) {
                $transaction->rollBack();
            }
            throw $e;
        }

        return $this->response;
    }
}
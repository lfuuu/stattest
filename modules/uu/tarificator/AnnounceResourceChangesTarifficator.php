<?php

namespace app\modules\uu\tarificator;

use app\classes\HandlerLogger;
use app\exceptions\FinanceException;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffChange as Log;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\Module;
use app\widgets\ConsoleProgress;
use Yii;
use yii\db\Expression;

class AnnounceResourceChangesTarifficator extends Tarificator
{
    /**
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     * @param bool $isWithTransaction
     * @throws \Exception
     * @throws \Throwable
     */
    public function tarificate($accountTariffId = null, $isWithTransaction = true)
    {
        $db = Yii::$app->db;

        $query = AccountTariffResourceLog::find()
            ->where(['is_announced' => 0])
            ->andWhere(['<=', 'actual_from_utc', new Expression('NOW()')])
            ->orderBy('id')
            ->with('accountTariff');

        // найти все ресурсы, изменения в которых надо проанонсировать
        if ($accountTariffId) {
            // только конкретную услугу, даже если не надо менять тариф
            $query->andWhere(['account_tariff_id' => $accountTariffId]);
        }

        $progress = new ConsoleProgress($query->count(), function ($string) {
            $this->out($string);
        });

        /** @var AccountTariffResourceLog $atl */
        foreach ($query->each() as $atl) {
            $progress->nextStep();

            $isWithTransaction && $transaction = $db->beginTransaction();
            try {

                $data = [
                    'account_tariff_id' => $atl->accountTariff->prev_account_tariff_id ?: $atl->accountTariff->id,
                    'amount' => $atl->amount,
                    'resource_id' => $atl->resource_id,
                    'actual_from_utc' => $atl->actual_from_utc,
                ];

                Log::add($atl->accountTariff->client_account_id, $atl->account_tariff_id, $data + ['action' => 'resource_add_applied']);

                $atl->is_announced = 1;

                if (!$atl->save(false, ['is_announced'])) {
                    throw new ModelValidationException($atl);
                }

                $isWithTransaction && $transaction->commit();

            } catch (\Exception $e) {
                $isWithTransaction && $transaction->rollBack();
                $this->out(PHP_EOL . 'Error. ' . $e->getMessage() . PHP_EOL);
                Yii::error($e->getMessage());
                HandlerLogger::me()->add($e->getMessage());
                if ($accountTariffId) {
                    throw $e;
                }
            }
        }
    }
}

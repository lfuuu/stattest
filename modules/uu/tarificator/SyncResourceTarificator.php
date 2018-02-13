<?php

namespace app\modules\uu\tarificator;

use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;
use Yii;
use yii\db\Expression;

/**
 * Отправить измененные ресурсы на платформу и другим поставщикам услуг
 */
class SyncResourceTarificator extends Tarificator
{
    /**
     * @param int|null $accountTariffId Если указан, то только для этой услуги. Если не указан - для всех
     * @param bool $isWithTransaction
     * @throws \Exception
     */
    public function tarificate($accountTariffId = null, $isWithTransaction = true)
    {
        $db = Yii::$app->db;

        // найти все услуги, у которых есть хоть один несинхронизированный ресурс
        $query = AccountTariffResourceLog::find()
            ->select(new Expression('DISTINCT account_tariff_id AS id'))
            ->where(['sync_time' => null])
            ->andWhere(['<=', 'actual_from_utc', DateTimeZoneHelper::getUtcDateTime()->format(DateTimeZoneHelper::DATETIME_FORMAT)])
            ->andWhere($accountTariffId ? ['account_tariff_id' => $accountTariffId] : [])
            ->asArray();

        foreach ($query->each() as $row) {

            $accountTariff = AccountTariff::findOne(['id' => $row['id']]);

            $isWithTransaction && $transaction = $db->beginTransaction();
            try {

                $accountTariffResourceLogs = $accountTariff->setResourceSyncTime();

                // только по незакрытым услугам
                if ($accountTariff->isActive()) {

                    // в зависимости от типа услуги
                    switch ($accountTariff->service_type_id) {

                        case ServiceType::ID_VOIP:
                            // Телефония
                            $number = $accountTariff->number;
                            EventQueue::go(\app\modules\uu\Module::EVENT_RESOURCE_VOIP, [
                                'client_account_id' => $accountTariff->client_account_id,
                                'account_tariff_id' => $accountTariff->id,
                                'number' => $accountTariff->voip_number,
                                'lines' => $accountTariff->getResourceValue(Resource::ID_VOIP_LINE),
                                'is_fmc_active' => ($number ? ($number->isFmcAlwaysActive() || (!$number->isFmcAlwaysInactive() && $accountTariff->getResourceValue(Resource::ID_VOIP_FMC))) : null),
                                'is_fmc_editable' => ($number ? $number->isFmcEditable() : null),
                                'is_mobile_outbound_active' => ($number ? ($number->isMobileOutboundAlwaysActive() || (!$number->isMobileOutboundAlwaysInactive() && $accountTariff->getResourceValue(Resource::ID_VOIP_MOBILE_OUTBOUND))) : null),
                                'is_mobile_outbound_editable' => ($number ? $number->isMobileOutboundEditable() : null),
                            ]);
                            break;

                        case ServiceType::ID_VPBX:
                            // ВАТС
                            EventQueue::go(\app\modules\uu\Module::EVENT_RESOURCE_VPBX, [
                                'client_account_id' => $accountTariff->client_account_id,
                                'account_tariff_id' => $accountTariff->id,
                            ]);
                            break;

                        case ServiceType::ID_VM_COLLOCATION:
                            // VM collocation
                            EventQueue::go(\app\modules\uu\Module::EVENT_RESOURCE_VM_COLLOCATION, [
                                'client_account_id' => $accountTariff->client_account_id,
                                'account_tariff_id' => $accountTariff->id,
                                'account_tariff_resource_ids' => array_keys($accountTariffResourceLogs),
                            ]);
                            break;
                    }
                }

                $isWithTransaction && $transaction->commit();

            } catch (\Exception $e) {
                $isWithTransaction && $transaction->rollBack();
                $this->out(PHP_EOL . 'Error. ' . $e->getMessage() . PHP_EOL);
                Yii::error($e->getMessage());
                if ($accountTariffId) {
                    throw $e;
                }
            }
        }
    }
}

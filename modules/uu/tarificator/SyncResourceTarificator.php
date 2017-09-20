<?php

namespace app\modules\uu\tarificator;

use app\classes\Event;
use app\helpers\DateTimeZoneHelper;
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

                // только по незакрытым услугам
                if ($accountTariff->isActive()) {

                    // в зависимости от типа услуги
                    switch ($accountTariff->service_type_id) {

                        case ServiceType::ID_VOIP:
                            // Телефония
                            $number = $accountTariff->number;
                            Event::go(Event::UU_ACCOUNT_TARIFF_RESOURCE_VOIP, [
                                'account_id' => $accountTariff->client_account_id,
                                'account_tariff_id' => $accountTariff->id,
                                'number' => $accountTariff->voip_number,
                                'lines' => $accountTariff->getResourceValue(Resource::ID_VOIP_LINE),
                                'is_fmc_active' => $number->isFmcAlwaysActive() || (!$number->isFmcAlwaysInactive() && $accountTariff->getResourceValue(Resource::ID_VOIP_FMC)),
                                'is_fmc_editable' => $number->isFmcEditable(),
                                'is_mobile_outbound_active' => $number->isMobileOutboundAlwaysActive() || (!$number->isMobileOutboundAlwaysInactive() && $accountTariff->getResourceValue(Resource::ID_VOIP_MOBILE_OUTBOUND)),
                                'is_mobile_outbound_editable' => $number->isMobileOutboundEditable(),
                            ]);
                            break;

                        case ServiceType::ID_VPBX:
                            // ВАТС
                            Event::go(Event::UU_ACCOUNT_TARIFF_RESOURCE_VPBX, [
                                'account_id' => $accountTariff->client_account_id,
                                'account_tariff_id' => $accountTariff->id,
                            ]);
                            break;

                    }
                }

                $accountTariff->setResourceSyncTime();

                $isWithTransaction && $transaction->commit();

            } catch (\Exception $e) {
                $isWithTransaction && $transaction->rollBack();
                $this->out(PHP_EOL . $e->getMessage() . PHP_EOL);
                Yii::error($e->getMessage());
                if ($accountTariffId) {
                    throw $e;
                }
            }
        }
    }
}

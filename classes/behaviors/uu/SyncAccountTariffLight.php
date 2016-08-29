<?php

namespace app\classes\behaviors\uu;

use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\ServiceType;
use app\modules\nnp\models\AccountTariffLight;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;


class SyncAccountTariffLight extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'AccountTariffLogChange',
            ActiveRecord::EVENT_AFTER_UPDATE => 'AccountTariffLogChange',
            ActiveRecord::EVENT_AFTER_DELETE => 'AccountTariffLogChange',
        ];
    }

    /**
     * Триггер при изменении лога тарифов
     * @param Event $event
     */
    public function AccountTariffLogChange(Event $event)
    {
        /** @var AccountTariffLog $accountTariffLog */
        $accountTariffLog = $event->sender;
        $accountTariff = $accountTariffLog->accountTariff;
        $this->_sync($accountTariff);
    }

    /**
     * Синхронизировать данные в AccountTariffLight
     * @param AccountTariff $accountTariff
     * @throws \Exception
     */
    protected function _sync(AccountTariff $accountTariff)
    {
        if ($accountTariff->service_type_id != ServiceType::ID_VOIP_PACKAGE) {
            // пока только для пакетов
            return;
        }

        // снаружи уже есть транзакция, но она на mysql, а тут на psql
        $db = AccountTariffLight::getDb();
        $transaction = $db->beginTransaction();
        try {

            // исходные данные
            $accountLogFromToTariffs = $accountTariff->getAccountLogHugeFromToTariffs(true);

            // ранее сконвертированные
            /** @var AccountTariffLight[] $accountTariffLights */
            $accountTariffLights = AccountTariffLight::find()
                ->where(['account_client_id' => $accountTariff->client_account_id])
                ->indexBy(function (AccountTariffLight $accountTariffLight) {
                    return $accountTariffLight->activate_from . '_' . $accountTariffLight->tariff_id;
                })
                ->all();

            // по всем исходным данным
            foreach ($accountLogFromToTariffs as $accountLogFromToTariff) {

                // должен быть такой же, как в indexBy при выборке $accountTariffLights!
                $uniqueId = $accountLogFromToTariff->dateFrom->format('Y-m-d') . '_' . $accountLogFromToTariff->tariffPeriod->id;

                if (isset($accountTariffLights[$uniqueId])) {
                    $accountTariffLight = $accountTariffLights[$uniqueId];
                    unset($accountTariffLights[$uniqueId]);
                } else {
                    $accountTariffLight = new AccountTariffLight;
                }
                if (!$accountTariff->prev_account_tariff_id) {
                    throw new \LogicException('Универсальная услуга ' . $accountTariff->id . ' пакета телефонии не привязана к основной услуге телефонии');
                }
                $accountTariffLight->number = $accountTariff->prevAccountTariff->voip_number;
                $accountTariffLight->account_client_id = $accountTariff->client_account_id;
                $accountTariffLight->tariff_id = $accountLogFromToTariff->tariffPeriod->tariff_id;
                $accountTariffLight->activate_from = $accountLogFromToTariff->dateFrom->format('Y-m-d');
                $accountTariffLight->deactivate_from = $accountLogFromToTariff->dateTo ? $accountLogFromToTariff->dateTo->format('Y-m-d') : null;
                if (!$accountTariffLight->save()) {
                    throw new \Exception(implode(' ', $accountTariffLight->getFirstErrors()));
                }

            }

            // оставшиеся ранее сконвертированные надо удалить
            foreach ($accountTariffLights as $accountTariffLight) {
                if (!$accountTariffLight->delete()) {
                    throw new \Exception(implode(' ', $accountTariffLight->getFirstErrors()));
                }
            }

            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

    }
}

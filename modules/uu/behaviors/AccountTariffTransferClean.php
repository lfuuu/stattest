<?php

namespace app\modules\uu\behaviors;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\models\usages\UsageInterface;
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\base\Behavior;
use yii\base\Event;
use yii\base\InvalidConfigException;


class AccountTariffTransferClean extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_DELETE => 'cleanTransfer',
        ];
    }

    /**
     * Если отменена услуга, и она была перенесенной - то отменить перенос.
     *
     * @param Event $event
     * @throws ModelValidationException
     * @throws \Exception
     */
    public function cleanTransfer(Event $event)
    {
        /** @var AccountTariff $accountTariff */
        $accountTariff = $event->sender;

        try {
            // не трансфернутая услуга или УУ-услуга.
            if (!$accountTariff->prev_usage_id || $accountTariff->prev_usage_id > AccountTariff::DELTA) {
                throw new InvalidConfigException('no need any actions');
            }

            // usage service
            if ($accountTariff->service_type_id == ServiceType::ID_VOIP || $accountTariff->service_type_id == ServiceType::ID_VPBX) {

                if ($accountTariff->service_type_id == ServiceType::ID_VOIP) {
                    $usage = UsageVoip::findOne(['id' => $accountTariff->prev_usage_id]);
                } else {
                    $usage = UsageVirtpbx::findOne(['id' => $accountTariff->prev_usage_id]);
                }

                if (!$usage) {
                    throw new InvalidConfigException('prev account tariff is not found');
                }

                $usage->next_usage_id = 0;
                $usage->actual_to = UsageInterface::MAX_POSSIBLE_DATE;

                if (!$usage->save()) {
                    throw new ModelValidationException($usage);
                }
            }
        } catch (InvalidConfigException $e) {
            return;
        } catch (\Exception $e) {
            throw $e;
        }

    }
}

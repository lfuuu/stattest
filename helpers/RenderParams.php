<?php
namespace app\helpers;

use Yii;
use app\classes\Assert;
use app\classes\Singleton;
use app\models\ClientAccount;
use app\models\Region;
use app\models\Country;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;

class RenderParams extends Singleton
{

    /**
     * @return []
     */
    public static function getListOfVariables()
    {
        $result = [];
        foreach (Yii::$app->params['mail_map_names'] as $key => $data) {
            $result[$key] = isset($data['descr']) ? $data['descr'] : 'Описание не найдено';
        }
        return $result;
    }

    /**
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call($name, $args)
    {
        return $name;
    }

    /**
     * @param string $tpl
     * @param int $clientAccountId
     * @param null|int $eventId
     * @return string
     * @throws \yii\base\Exception
     */
    public function apply($tpl, $clientAccountId, $eventId = null)
    {
        Assert::isNotEmpty($tpl);

        foreach (Yii::$app->params['mail_map_names'] as $replaceFrom => $data) {
            if (!isset($data['method'])) {
                continue;
            }
            $replaceTo = $this->{$data['method']}($clientAccountId, $eventId);
            $tpl = str_replace($replaceFrom, $replaceTo, $tpl);
        }

        return $tpl;
    }

    /**
     * @param int $clientAccountId
     * @return int
     */
    private function getClientAccountId($clientAccountId)
    {
        return $clientAccountId;
    }

    /**
     * @param int $clientAccountId
     * @return int
     */
    private function getContractNum($clientAccountId)
    {
        return ClientAccount::findOne($clientAccountId)->contract_id;
    }

    /**
     * @param int $clientAccountId
     * @return string
     */
    private function getBalance($clientAccountId)
    {
        return sprintf('%0.2f', ClientAccount::findOne($clientAccountId)->billingCounters->realtimeBalance);
    }

    /**
     * @param int $clientAccountId
     * @return string
     */
    private function getLnk($clientAccountId)
    {
        $region_id = ClientAccount::findOne($clientAccountId)->region;
        $country_id = Region::findOne($region_id)->country_id;
        $lkPrefix = Yii::t('settings', 'lk_domain', [], Country::findOne(['code' => $country_id])->lang);
        return $lkPrefix . 'core/auth/activate?token=<token>';
    }

    /**
     * @param int $clientAccountId
     * @return string
     */
    private function getClientAccountCurrency($clientAccountId)
    {
        $clientAccount = ClientAccount::findOne($clientAccountId);
        return $clientAccount->currency;
    }

    /**
     * @param int $clientAccountId
     * @return float
     */
    private function getClientAccountDayLimit($clientAccountId)
    {
        $clientAccount = ClientAccount::findOne($clientAccountId);
        return $clientAccount->voip_credit_limit_day;
    }

    /**
     * @param $clientAccountId
     * @return int
     */
    private function getClientAccountMinDayLimit($clientAccountId)
    {
        $clientAccount = ClientAccount::findOne($clientAccountId);
        return $clientAccount->lkSettings->{ImportantEventsNames::IMPORTANT_EVENT_MIN_DAY_LIMIT};
    }

    /**
     * @param int $clientAccountId
     * @param int $eventId
     * @return float
     */
    private function getNewPaymentValue($clientAccountId, $eventId)
    {
        return (float)$this->eventProperty($clientAccountId, $eventId, 'sum');
    }

    /**
     * @param int $clientAccountId
     * @param int $eventId
     * @param string|false $eventProperty
     * @return string|array
     */
    private function eventProperty($clientAccountId, $eventId, $eventProperty = false)
    {
        /** @var ImportantEvents $event */
        if (($event = ImportantEvents::findOne([
                'client_id' => $clientAccountId,
                'id' => $eventId
            ])) === null
        ) {
            return false;
        }

        return
            $eventProperty !== false && isset($event->properties[$eventProperty])
                ? (string)$event->properties[$eventProperty]
                : $event->properties;

    }

}

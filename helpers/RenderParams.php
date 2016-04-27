<?php
namespace app\helpers;

use Yii;
use app\classes\Singleton;
use app\models\ClientAccount;
use app\models\Region;
use app\models\Country;
use app\models\important_events\ImportantEvents;
use yii\helpers\ArrayHelper;

class RenderParams extends Singleton
{

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
    *
    * @param string $tpl
    * @param int $clientAccountId
    * @param int $contactId
    **/
    public function apply($tpl, $clientAccountId, $contactId, $eventId = null)
    {
        assert(!empty($tpl));
        foreach(Yii::$app->params['mail_map_names'] as $replaceFrom => $call) {
            $replaceTo = $this->{$call}($clientAccountId, $contactId, $eventId);
            $tpl = str_replace($replaceFrom, $replaceTo, $tpl);
        }
        return $tpl;
    }

    /**
    * @param int $clientAccountId
    * @param int $contactId
    **/
    private function getClientAccountId($clientAccountId, $contactId)
    {
        return $clientAccountId;
    }

    /**
    * @param int $clientAccountId
    * @param int $contactId
    **/
    private function getContactId($clientAccountId, $contactId)
    {
        return $contactId;
    }

    /**
    * @param int $clientAccountId
    * @param int $contactId
    **/
    private function getKey($clientAccountId, $contactId)
    {
        return 'key';
    }

    /**
    * @param int $clientAccountId
    * @param int $contactId
    **/
    private function getContractNum($clientAccountId, $contactId)
    {
        return ClientAccount::findOne($clientAccountId)->contract_id;
    }

    /**
    * @param int $clientAccountId
    * @param int $contactId
    **/
    private function getBalance($clientAccountId, $contactId)
    {
        return ClientAccount::findOne($clientAccountId)->getRealtimeBalance();
    }

    /**
    * @param int $clientAccountId
    * @param int $contactId
    **/
    private function getLnk($clientAccountId, $contactId)
    {
        $region_id = ClientAccount::findOne($clientAccountId)->region;
        $country_id = Region::findOne($region_id)->country_id;
        $lkPrefix = Yii::t('settings', 'lk_domain', [], Country::findOne(['code' => $country_id])->lang);
        return $lkPrefix . 'core/auth/activate?token=<token>';
    }

    /**
    * @param int $clientAccountId
    * @param int $contactId
    **/
    private function getPassword($clientAccountId, $contactId)
    {
        return 'anyPass';
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
        return $clientAccount->lkSettings->day_limit;
    }

    /**
     * @param int $eventId
     * @param string $eventProperty
     * @param boolean $allMode
     */
    private function getNewPaymentValue($clientAccountId, $contactId, $eventId)
    {
        return $this->eventProperty($clientAccountId, $eventId, 'value');
    }

    /**
     * @param int $clientAccountId
     * @param int $eventId
     * @param string|false $eventProperty
     * @return string|array
     */
    private function eventProperty($clientAccountId, $eventId, $eventProperty = false)
    {
        $event = ImportantEvents::findOne([
            'client_id' => $clientAccountId,
            'id' => $eventId
        ]);

        $properties = ArrayHelper::map((array) $event->properties, 'property', 'value');

        return
            $eventProperty !== false && isset($properties[$eventProperty])
                ? $properties[$eventProperty]
                : $properties;

    }

}

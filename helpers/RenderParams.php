<?php
namespace app\helpers;

use Yii;
use app\classes\Assert;
use app\classes\Singleton;
use app\classes\important_events\ImportantEventsDetailsFactory;
use app\models\ClientAccount;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;

class RenderParams extends Singleton
{

    /**
     * @return array
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

        $that = $this;
        $mailVariables = Yii::$app->params['mail_map_names'];

        if ((int)$eventId) {
            /** @var ImportantEvents $event */
            $event = ImportantEvents::findOne(['id' => $eventId]);
        }

        $tpl = preg_replace_callback(
            '#\{([a-zA-Z0-9_\.]+)\}#',
            function($match) use ($that, $mailVariables, $clientAccountId, $event) {
                $method = $match[1];
                if (array_key_exists($method, $mailVariables) && method_exists($that, $mailVariables[$method]['method'])) {
                    return $that->{$mailVariables[$method]['method']}($clientAccountId);
                }
                if (!is_null($event) && strpos($method, 'event.') === 0) {
                    return $that->getEventProperty($event, str_replace('event.', '', $method));
                }
                return '';
            },
            $tpl
        );

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
     * @param ImportantEvents $event
     * @param $eventProperty
     * @return mixed
     * @throws \yii\base\Exception
     */
    private function getEventProperty(ImportantEvents $event, $eventProperty)
    {
        return ImportantEventsDetailsFactory::get($event->event, $event)->getProperty($eventProperty);
    }

}

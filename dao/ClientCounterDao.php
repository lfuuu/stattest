<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\billing\Counter;
use app\models\ClientCounter;

/**
 * @method static ClientCounterDao me($args = null)
 * @property
 */
class ClientCounterDao extends Singleton
{

    private static $amounts = [];

    public function getOrCreateCounter($clientAccountId)
    {
        $counter = ClientCounter::findOne($clientAccountId);

        if (!$counter) {
            $counter = new ClientCounter();
            $counter->client_id = $clientAccountId;
            $counter->save();

            $counter = ClientCounter::findOne($clientAccountId);
        }

        return $counter;
    }

    public static function getAmountSumByAccountId($clientId)
    {
        if (isset(static::$amounts[$clientId])) {
            return static::$amounts[$clientId];
        }


        $isErrorLoad = false;

        try {
            $pgCounter = Counter::findOne(['client_id' => $clientId]);
        } catch (\Exception $e) {
            $isErrorLoad = true;
        }

        $result = ['amount_sum' => 0, 'amount_day_sum' => 0, 'amount_month_sum' => 0];

        if (!$isErrorLoad) {
            if ($pgCounter) {
                $counter = ClientCounter::dao()->getOrCreateCounter($clientId);
                $counter->setAttributes($pgCounter->getAttributes());
                $counter->save();
                $result = $pgCounter->toArray();
            } else {
                $counter = ClientCounter::findOne($clientId);
                if ($counter) {
                    $counter->delete();
                }
            }
        } else {
            $counters = ClientCounter::findOne(['client_id' => $clientId]);
            if ($counters) {
                $result = $counters->toArray();
            }
        }

        static::$amounts[$clientId] = $result;

        return $result;
    }

}

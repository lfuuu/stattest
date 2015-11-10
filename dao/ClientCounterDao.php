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
        $pgCounter = Counter::findOne(['client_id' => $clientId]);
        if ($pgCounter) {
            $counter = ClientCounter::findOne($clientId);
            if (!$counter) {
                $counter = new ClientCounter();
            }
            $counter->setAttributes($pgCounter->getAttributes());
            $counter->save();
        }
        else {
            $counter = ClientCounter::findOne($clientId);
            if ($counter) {
                $counter->delete();
            }
        }
        $result = ($pgCounter) ? $pgCounter->toArray() : ['amount_sum' => 0, 'amount_day_sum' => 0, 'amount_month_sum' => 0];
        static::$amounts[$clientId] = $result;
        return $result;
    }

}
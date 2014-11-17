<?php
namespace app\dao;

use app\classes\Singleton;
use app\models\ClientCounter;

/**
 * @method static ClientCounterDao me($args = null)
 * @property
 */
class ClientCounterDao extends Singleton
{

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

}
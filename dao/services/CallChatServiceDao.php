<?php

namespace app\dao\services;

use Yii;
use app\classes\Singleton;
use app\models\ClientAccount;
use app\models\UsageCallChat;

class CallChatServiceDao extends Singleton implements ServiceDao
{

    public function getPossibleToTransfer(ClientAccount $client)
    {
        return
            UsageCallChat::find()
                ->innerJoinWith('tariff', false)
                ->client($client->client)
                ->actual()
                ->andWhere(['next_usage_id' => 0])
                ->andWhere(['tarifs_call_chat.status' => ['public', 'special', 'archive']])
                ->all();
    }

}

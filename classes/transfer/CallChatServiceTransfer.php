<?php

namespace app\classes\transfer;

use app\models\ClientAccount;
use app\models\UsageCallChat;

class CallChatServiceTransfer extends ServiceTransfer
{

    /**
     * Список услуг доступных для переноса
     *
     * @param ClientAccount $clientAccount
     * @return UsageCallChat[]
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        return
            UsageCallChat::find()
                ->innerJoinWith('tariff', false)
                ->client($clientAccount->client)
                ->actual()
                ->andWhere(['next_usage_id' => 0])
                ->andWhere(['tarifs_call_chat.status' => ['public', 'special', 'archive']])
                ->all();
    }

}
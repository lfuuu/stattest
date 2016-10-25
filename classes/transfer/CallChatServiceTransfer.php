<?php

namespace app\classes\transfer;

use app\models\ClientAccount;
use app\models\TariffCallChat;
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
                ->andWhere([
                    'tarifs_call_chat.status' => [
                        TariffCallChat::CALL_CHAT_TARIFF_STATUS_PUBLIC,
                        TariffCallChat::CALL_CHAT_TARIFF_STATUS_SPECIAL,
                        TariffCallChat::CALL_CHAT_TARIFF_STATUS_ARCHIVE,
                    ]
                ])
                ->all();
    }

}
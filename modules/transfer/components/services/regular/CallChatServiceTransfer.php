<?php

namespace app\modules\transfer\components\services\regular;

use app\classes\Assert;
use app\models\ClientAccount;
use app\models\TariffCallChat;
use app\models\UsageCallChat;
use app\modules\uu\models\ServiceType;

class CallChatServiceTransfer extends BasicServiceTransfer
{

    /** @var UsageCallChat */
    private $_service;

    /**
     * @return string
     */
    public function getServiceModelName()
    {
        return UsageCallChat::className();
    }

    /**
     * @return int
     */
    public function getServiceTypeId()
    {
        return ServiceType::ID_CALL_CHAT;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return UsageCallChat[]
     */
    public function getPossibleToTransfer(ClientAccount $clientAccount)
    {
        return UsageCallChat::find()
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

    /**
     * @param int $serviceId
     * @return $this
     * @throws \yii\base\Exception
     */
    public function setServiceById($serviceId)
    {
        $this->_service = UsageCallChat::findOne(['id' => $serviceId]);
        Assert::isObject($this->_service, 'Missing UsageCallChat #' . $serviceId);

        return $this;
    }

    /**
     * @return UsageCallChat
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * @param UsageCallChat $service
     */
    public function setService($service)
    {
        $this->_service = $service;
    }

    /**
     * @return array
     */
    public function getBaseAttributes()
    {
        return [
            'service_type_id' => $this->getServiceTypeId(),
        ];
    }

}
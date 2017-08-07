<?php

namespace tests\codeception\unit\transfer;

use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\TariffCallChat;
use app\models\UsageCallChat;
use app\modules\transfer\components\services\Processor;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use app\tests\codeception\fixtures\TariffCallChatFixture;

class CallChatServiceTest extends _BaseService
{

    /**
     * Load fixtures
     *
     * @throws ModelValidationException
     * @throws \Exception
     */
    protected function load()
    {
        // Loading fixtures regular fixtures
        (new TariffCallChatFixture)->load();

        parent::load();
    }

    /**
     * Unload fixtures
     */
    protected function unload()
    {
        (new TariffCallChatFixture)->unload();

        parent::unload();
    }

    /**
     * @return string
     */
    protected function getServiceTypeCode()
    {
        return Processor::SERVICE_CALL_CHAT;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return UsageCallChat
     * @throws ModelValidationException
     */
    protected function createRegularService(ClientAccount $clientAccount)
    {
        // Select available tariff
        $tariffId = TariffCallChat::find()->select('MAX(id)')->scalar();

        // Creating service (Can't use setAttributes method. Model has missing rules)
        $service = new UsageCallChat;
        $service->actual_from = $this->getActivationDate();
        $service->actual_to = $this->getExpireDate();
        $service->client = $clientAccount->client;
        $service->tarif_id = $tariffId;

        if (!$service->save()) {
            throw new ModelValidationException($service);
        }

        return $service;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return AccountTariff
     */
    protected function createUniversalService(ClientAccount $clientAccount)
    {
        return $this->createUniversalServiceSimple($clientAccount, ServiceType::ID_CALL_CHAT, $this->getTariffForUniversalService());
    }

    /**
     * @param null $service
     * @return int
     */
    protected function getTariffForUniversalService($service = null)
    {
        return $this->getUniversalTariff(ServiceType::ID_CALL_CHAT);
    }

}
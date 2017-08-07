<?php

namespace tests\codeception\unit\transfer;

use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\TariffSms;
use app\models\UsageSms;
use app\modules\transfer\components\services\Processor;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use app\tests\codeception\fixtures\TariffSmsFixture;

class SmsServiceTest extends _BaseService
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
        (new TariffSmsFixture)->load();

        parent::load();
    }

    /**
     * Unload fixtures
     */
    protected function unload()
    {
        (new TariffSmsFixture)->unload();

        parent::unload();
    }

    /**
     * @return string
     */
    protected function getServiceTypeCode()
    {
        return Processor::SERVICE_SMS;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return UsageSms
     * @throws ModelValidationException
     */
    protected function createRegularService(ClientAccount $clientAccount)
    {
        // Select available tariff
        $tariffId = TariffSms::find()->select('MAX(id)')->scalar();

        // Creating service (Can't use setAttributes method. Model has missing rules)
        $service = new UsageSms;
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
        return $this->createUniversalServiceSimple($clientAccount, ServiceType::ID_SMS, $this->getTariffForUniversalService());
    }

    /**
     * @param null $service
     * @return int
     */
    protected function getTariffForUniversalService($service = null)
    {
        return $this->getUniversalTariff(ServiceType::ID_SMS);
    }

}
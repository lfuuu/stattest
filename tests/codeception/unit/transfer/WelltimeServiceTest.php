<?php

namespace tests\codeception\unit\transfer;

use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\TariffExtra;
use app\models\UsageSms;
use app\models\UsageWelltime;
use app\modules\transfer\components\services\Processor;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use app\tests\codeception\fixtures\TariffExtraFixture;

class WelltimeServiceTest extends _BaseService
{

    /**
     * @return string
     */
    protected function getServiceTypeCode()
    {
        return Processor::SERVICE_WELLTIME_SAAS;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return UsageSms
     * @throws ModelValidationException
     */
    protected function createRegularService(ClientAccount $clientAccount)
    {
        // Select available tariff
        $tariffId = TariffExtra::find()
            ->select('MAX(id)')
            ->where(['code' => 'welltime'])
            ->scalar();

        // Creating service (Can't use setAttributes method. Model has missing rules)
        $service = new UsageWelltime;
        $service->actual_from = $this->getActivationDate();
        $service->actual_to = $this->getExpireDate();
        $service->client = $clientAccount->client;
        $service->tarif_id = $tariffId;
        $service->ip = '127.0.0.1';
        $service->router = 'test router';

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
        return $this->createUniversalServiceSimple($clientAccount, ServiceType::ID_WELLTIME_SAAS, $this->getTariffForUniversalService());
    }

    /**
     * @param null $service
     * @return int
     */
    protected function getTariffForUniversalService($service = null)
    {
        return $this->getUniversalTariff(ServiceType::ID_WELLTIME_SAAS);
    }

}
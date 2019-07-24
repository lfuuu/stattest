<?php

namespace tests\codeception\unit\transfer;

use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\Region;
use app\models\TariffVirtpbx;
use app\models\UsageVirtpbx;
use app\modules\transfer\components\services\Processor;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;

class VirtpbxServiceTest extends _BaseService
{

    /**
     * @return string
     */
    protected function getServiceTypeCode()
    {
        return Processor::SERVICE_VPBX;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return UsageVirtpbx
     * @throws ModelValidationException
     */
    protected function createRegularService(ClientAccount $clientAccount)
    {
        // Select available tariff
        $tariffId = TariffVirtpbx::find()->select('MAX(id)')->scalar();

        // Creating service (Can't use setAttributes method. Model has missing rules)
        $service = new UsageVirtpbx;
        $service->region = Region::MOSCOW;
        $service->actual_from = $this->getActivationDate();
        $service->actual_to = $this->getExpireDate();
        $service->client = $clientAccount->client;
        $service->tarif_id = $tariffId;
        $service->moved_from = 0;

        if (!$service->save()) {
            throw new ModelValidationException($service);
        }

        $this->setRegularTariff($service, $tariffId);

        return $service;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return AccountTariff
     * @throws ModelValidationException
     */
    protected function createUniversalService(ClientAccount $clientAccount)
    {
        return $this->createUniversalServiceSimple($clientAccount, ServiceType::ID_VPBX, $this->getTariffForUniversalService(), ['region_id' => Region::MOSCOW]);
    }

    /**
     * @param null $service
     * @return int
     */
    protected function getTariffForUniversalService($service = null)
    {
        return $this->getUniversalTariff(ServiceType::ID_VPBX);
    }

}
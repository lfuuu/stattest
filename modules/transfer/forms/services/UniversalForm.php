<?php

namespace app\modules\transfer\forms\services;

use app\modules\transfer\components\services\ServiceTransfer;
use app\modules\transfer\forms\services\decorators\UniversalServiceDecorator;
use app\modules\uu\models\AccountTariff;

/**
 * @property-read array $servicesPossibleToTransfer
 */
class UniversalForm extends BaseForm
{

    /**
     * @return array
     */
    public function getServicesPossibleToTransfer()
    {
        $result = [];

        foreach ($this->processor->getServices() as $serviceKey => $serviceClass) {
            /** @var ServiceTransfer $serviceHandler */
            $serviceHandler = new $serviceClass;
            $services = $serviceHandler->getPossibleToTransfer($this->clientAccount);

            if (!count($services)) {
                continue;
            }

            $firstService = reset($services);

            $result[$serviceKey] = [
                'title' => $firstService->serviceType->name,
                'services' => array_map(function ($service) use ($serviceHandler) {
                    return $serviceHandler->getServiceDecorator($service);
                }, $services),
            ];
        }

        return $result;
    }

}
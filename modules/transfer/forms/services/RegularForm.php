<?php

namespace app\modules\transfer\forms\services;

use app\modules\transfer\components\services\ServiceTransfer;
use yii\base\InvalidParamException;

/**
 * @property-read array $servicesPossibleToTransfer
 */
class RegularForm extends BaseForm
{

    /**
     * @return array
     * @throws InvalidParamException
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
                'title' => $firstService->helper->title,
                'services' => array_map(function ($service) use ($serviceHandler) {
                    return $serviceHandler->getServiceDecorator($service);
                }, $services),
            ];
        }

        return $result;
    }

}
<?php

namespace app\modules\transfer\components\services\universal;

use app\exceptions\ModelValidationException;
use app\modules\transfer\components\services\PreProcessor;
use app\modules\uu\models\ServiceType;

class SmsServiceTransfer extends BasicServiceTransfer
{

    /**
     * @return int
     */
    public function getServiceTypeId()
    {
        return ServiceType::ID_VOIP_PACKAGE_SMS;
    }

    /**
     * @param PreProcessor $preProcessor
     * @throws ModelValidationException
     */
    public function finalizeClose(PreProcessor $preProcessor)
    {
        // Ничего не делать в случае переноса "Универсальная услуга" => "Универсальная услуга"
        // Для универсальных услуг метод переопределяет родительский, ОБЯЗАТЕЛЬНО ДОЛЖЕН НИЧЕГО НЕ ДЕЛАТЬ
    }

}
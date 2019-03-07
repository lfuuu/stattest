<?php

namespace app\modules\uu\forms;

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;

/**
 * Class AccountTariffAddForm
 */
class AccountTariffAddForm extends AccountTariffForm
{
    /** @var int ID клиента */
    public $clientAccountId = null;

    /** @var int */
    public $cityId = null;

    /**
     * @return AccountTariff
     */
    public function getAccountTariffModel()
    {
        $accountTariff = new AccountTariff();
        $accountTariff->client_account_id = $this->clientAccountId;
        $accountTariff->service_type_id = $this->serviceTypeId;
        $accountTariff->city_id = $this->cityId;
        return $accountTariff;
    }

    /**
     * Показывать ли предупреждение, что необходимо выбрать клиента
     *
     * @return bool
     */
    public function getIsNeedToSelectClient()
    {
        return !$this->clientAccountId;
    }

    /**
     * Проверка что услуга - Телефония, ВАТС или Чатофон
     * Используется для автоматического создания заявки
     * BIL-4828
     *
     * @return bool
     */
    public function isShowRoistatVisit()
    {
        return $this->serviceTypeId == ServiceType::ID_VPBX
            || $this->serviceTypeId == ServiceType::ID_VOIP
            || $this->serviceTypeId == ServiceType::ID_CALL_CHAT;
    }
}
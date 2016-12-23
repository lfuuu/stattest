<?php

namespace app\classes\uu\forms;

use app\classes\uu\model\AccountTariff;

/**
 * Class AccountTariffAddForm
 */
class AccountTariffAddForm extends AccountTariffForm
{
    /** @var int ID клиента */
    public $clientAccountId = null;

    /**
     * Конструктор
     */
    public function init()
    {
        global $fixclient_data;
        if (isset($fixclient_data['id']) && $fixclient_data['id'] > 0) {
            $this->clientAccountId = $fixclient_data['id'];
        }

        parent::init();
    }

    /**
     * @return AccountTariff
     */
    public function getAccountTariffModel()
    {
        $accountTariff = new AccountTariff();
        $accountTariff->client_account_id = $this->clientAccountId;
        $accountTariff->service_type_id = $this->serviceTypeId;
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
}
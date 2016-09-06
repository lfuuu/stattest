<?php
namespace app\classes\behaviors\uu;


use app\classes\api\ApiVmCollocation;
use app\classes\Utils;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\Resource;
use app\classes\uu\model\ServiceType;
use app\models\ClientAccount;
use app\models\ClientAccountOptions;
use yii\base\InvalidParamException;

class SyncVmCollocation
{
    const EVENT_SYNC = 'uu_vm_collocation_sync';

    const CLIENT_ACCOUNT_OPTION_VM_ELID = 'vm_elid'; // ID клиента в VM
    const CLIENT_ACCOUNT_OPTION_VM_PASSWORD = 'vm_password'; // Пароль клиента в VM

    /**
     * Синхронизировать в VM manager
     * @param int $accountTariffId
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=3508161
     */
    public function syncVm($accountTariffId)
    {
        if (
            !$accountTariffId ||
            !($accountTariff = AccountTariff::findOne(['id' => $accountTariffId])) ||
            $accountTariff->service_type_id != ServiceType::ID_VM_COLLOCATION
        ) {
            throw new InvalidParamException('SyncVmCollocation. Неправильный параметр ' . $accountTariffId);
        }

        $apiVmCollocation = ApiVmCollocation::getInstance();

        if (!$accountTariff->tariff_period_id) {
            // выключить
            if ($accountTariff->vm_elid_id) {
                $apiVmCollocation->dropVps($accountTariff->vm_elid_id);
                // ... и запомнить
                $accountTariff->vm_elid_id = null;
                if (!$accountTariff->save()) {
                    throw new \LogicException(implode(' ', $accountTariff->getFirstErrors()));
                }
            }
            return;
        }

        $vmClientId = $this->getVmClientId($accountTariff->clientAccount);
        $tariff = $accountTariff->tariffPeriod->tariff;
        if ($accountTariff->vm_elid_id) {

            // уже есть - обновить
            $tariffResources = $tariff->tariffResources;
            $apiVmCollocation->updateVps(
                $accountTariff->vm_elid_id,
                $resourceRam = $tariffResources[Resource::ID_VM_COLLOCATION_RAM],
                $resourceHdd = $tariffResources[Resource::ID_VM_COLLOCATION_HDD],
                $resourceProcessor = $tariffResources[Resource::ID_VM_COLLOCATION_PROCESSOR]
            );

        } else {

            // еще нет - создать
            $password = Utils::password_gen();
            $vmElidId = $apiVmCollocation->createVps(
                $name = 'vps_' . $accountTariffId,
                $password,
                $domain = 'example.com',
                $preset = $tariff->vm_id,
                $vmClientId
            );
            if (!$vmElidId) {
                throw new InvalidParamException('Ошибка создания VM collocation ' . $accountTariffId);
            }
            // ... и запомнить
            $accountTariff->vm_elid_id = $vmElidId;
            if (!$accountTariff->save()) {
                throw new \LogicException(implode(' ', $accountTariff->getFirstErrors()));
            }

        }
    }

    /**
     * Включить клиента в VM, если он там есть
     * @param int $clientAccountId
     * @return int
     */
    public function enableAccount($clientAccountId)
    {
        $this->enableOrDisableAccount($clientAccountId, $isEnable = true);
    }

    /**
     * Включить клиента в VM, если он там есть
     * @param int $clientAccountId
     * @return int
     */
    public function disableAccount($clientAccountId)
    {
        $this->enableOrDisableAccount($clientAccountId, $isEnable = false);
    }

    /**
     * Включить клиента в VM, если он там есть
     * @param int $clientAccountId
     * @param bool $isEnable
     * @return int
     */
    protected function enableOrDisableAccount($clientAccountId, $isEnable)
    {
        $clientAccount = ClientAccount::findOne(['id' => $clientAccountId]);
        $vmClientId = $this->getVmClientId($clientAccount, $isCreate = false);
        if (!$vmClientId) {
            return false;
        }
        $apiVmCollocation = ApiVmCollocation::getInstance();
        return $apiVmCollocation->enableOrDisableUser($vmClientId, $isEnable);
    }

    /**
     * Вернуть ID клиента в VM
     * @param ClientAccount $clientAccount
     * @param bool $isCreate создавать ли, если не найден ранее созданный
     * @return int
     */
    protected function getVmClientId(ClientAccount $clientAccount, $isCreate = true)
    {
        // взять из кэша
        $options = $clientAccount->getOption(self::CLIENT_ACCOUNT_OPTION_VM_ELID);
        if (count($options)) {
            $vmClientId = (int)$options[0];
        } else {
            $vmClientId = null;
        }
        if ($vmClientId) {
            return $vmClientId;
        }

        if (!$isCreate) {
            return null;
        }

        $apiVmCollocation = ApiVmCollocation::getInstance();
        $vmClientId = $apiVmCollocation->createUser($clientAccount->client, $password = Utils::password_gen());
        if (!$vmClientId) {
            throw new \LogicException('Ошибка создания клиента в VmCollocation');
        }

        // сохранить в кэш
        // id
        $clientAccountOptions = new ClientAccountOptions;
        $clientAccountOptions->client_account_id = $clientAccount->id;
        $clientAccountOptions->option = self::CLIENT_ACCOUNT_OPTION_VM_ELID;
        $clientAccountOptions->value = (string)$vmClientId;
        if (!$clientAccountOptions->save()) {
            throw new \LogicException(implode(' ', $clientAccountOptions->getFirstErrors()));
        }
        unset($clientAccountOptions);

        // пароль
        $clientAccountOptions = new ClientAccountOptions;
        $clientAccountOptions->client_account_id = $clientAccount->id;
        $clientAccountOptions->option = self::CLIENT_ACCOUNT_OPTION_VM_PASSWORD;
        $clientAccountOptions->value = (string)$password;
        if (!$clientAccountOptions->save()) {
            throw new \LogicException(implode(' ', $clientAccountOptions->getFirstErrors()));
        }
        unset($clientAccountOptions);

        return $vmClientId;
    }

}
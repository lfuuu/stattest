<?php
namespace app\modules\uu\behaviors;


use app\classes\api\ApiVmCollocation;
use app\classes\Utils;
use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\ClientAccountOptions;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;
use yii\base\InvalidParamException;

class SyncVmCollocation
{
    const EVENT_SYNC = 'uu_vm_collocation_sync';

    const CLIENT_ACCOUNT_OPTION_VM_ELID = 'vm_elid'; // ID клиента в VM
    const CLIENT_ACCOUNT_OPTION_VM_PASSWORD = 'vm_password'; // Пароль клиента в VM

    /**
     * Синхронизировать в VM manager
     *
     * @param int $accountTariffId
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=3508161
     * @throws \app\exceptions\ModelValidationException
     * @throws \yii\base\InvalidCallException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
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

        $apiVmCollocation = ApiVmCollocation::me();

        if (!$accountTariff->tariff_period_id) {
            // выключить
            if ($accountTariff->vm_elid_id) {
                $apiVmCollocation->dropVps($accountTariff->vm_elid_id);
                // ... и запомнить
                $accountTariff->vm_elid_id = null;
                if (!$accountTariff->save()) {
                    throw new ModelValidationException($accountTariff);
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
                throw new ModelValidationException($accountTariff);
            }
        }
    }

    /**
     * Включить клиента в VM, если он там есть
     *
     * @param int $clientAccountId
     * @return mixed
     */
    public function enableAccount($clientAccountId)
    {
        return $this->enableOrDisableAccount($clientAccountId, $isEnable = true);
    }

    /**
     * Включить клиента в VM, если он там есть
     *
     * @param int $clientAccountId
     * @return mixed
     */
    public function disableAccount($clientAccountId)
    {
        return $this->enableOrDisableAccount($clientAccountId, $isEnable = false);
    }

    /**
     * Включить клиента в VM, если он там есть
     *
     * @param int $clientAccountId
     * @param bool $isEnable
     * @return mixed
     */
    protected function enableOrDisableAccount($clientAccountId, $isEnable)
    {
        $apiVmCollocation = ApiVmCollocation::me();
        if (!$apiVmCollocation->isAvailable()) {
            return null;
        }

        $clientAccount = ClientAccount::findOne(['id' => $clientAccountId]);
        $vmClientId = (int)$this->getVmUserInfo($clientAccount);
        if (!$vmClientId) {
            return false;
        }

        return $apiVmCollocation->enableOrDisableUser($vmClientId, $isEnable);
    }

    /**
     * Вернуть ID клиента в VM
     *
     * @param ClientAccount $clientAccount
     * @return int
     * @throws \app\exceptions\ModelValidationException
     */
    protected function getVmClientId(ClientAccount $clientAccount)
    {
        // взять из кэша
        $vmClientId = (int)$this->getVmUserInfo($clientAccount);
        if ($vmClientId) {
            return $vmClientId;
        }

        $apiVmCollocation = ApiVmCollocation::me();
        $vmClientId = $apiVmCollocation->createUser($name = 'client_' . $clientAccount->id, $password = Utils::password_gen());
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
            throw new ModelValidationException($clientAccountOptions);
        }

        unset($clientAccountOptions);

        // пароль
        $clientAccountOptions = new ClientAccountOptions;
        $clientAccountOptions->client_account_id = $clientAccount->id;
        $clientAccountOptions->option = self::CLIENT_ACCOUNT_OPTION_VM_PASSWORD;
        $clientAccountOptions->value = (string)$password;
        if (!$clientAccountOptions->save()) {
            throw new ModelValidationException($clientAccountOptions);
        }

        unset($clientAccountOptions);

        return $vmClientId;
    }

    /**
     * @param ClientAccount $clientAccount
     * @param string $option
     * @return null|string
     */
    public function getVmUserInfo(ClientAccount $clientAccount, $option = self::CLIENT_ACCOUNT_OPTION_VM_ELID)
    {
        $options = $clientAccount->getOption($option);
        if (count($options)) {
            return $options[0];
        } else {
            return null;
        }
    }
}
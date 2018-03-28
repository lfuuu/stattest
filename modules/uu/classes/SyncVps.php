<?php

namespace app\modules\uu\classes;


use app\classes\api\ApiVps;
use app\classes\Utils;
use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\ClientAccountOptions;
use app\models\Trouble;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\Resource;
use app\modules\uu\models\ServiceType;
use yii\base\InvalidParamException;

class SyncVps
{
    const CLIENT_ACCOUNT_OPTION_VPS_ELID = 'vm_elid'; // ID клиента в VPS
    const CLIENT_ACCOUNT_OPTION_VPS_PASSWORD = 'vm_password'; // Пароль клиента в VPS

    /**
     * Синхронизировать в VPS manager
     *
     * @param int $accountTariffId
     * @link http://confluence.welltime.ru/pages/viewpage.action?pageId=3508161
     * @throws \yii\db\Exception
     * @throws \Exception
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
            $accountTariff->service_type_id != ServiceType::ID_VPS
        ) {
            throw new InvalidParamException('SyncVps. Неправильный параметр ' . $accountTariffId);
        }

        $apiVps = ApiVps::me();

        if (!$accountTariff->tariff_period_id) {
            // выключить
            if ($accountTariff->vm_elid_id) {
                $apiVps->vpsStop($accountTariff->vm_elid_id);
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
            $apiVps->vpsUpdate(
                $accountTariff->vm_elid_id,
                $resourceRam = (int)$tariffResources[Resource::ID_VPS_RAM],
                $resourceProcessor = (int)$tariffResources[Resource::ID_VPS_PROCESSOR]
            );

        } else {

            // еще нет - создать
            $password = Utils::password_gen();
            $vmElidId = $apiVps->vpsCreate(
                $name = 'vps_' . $accountTariffId,
                $password,
                $domain = 'example.com',
                $preset = $tariff->vm_id,
                $vmClientId
            );
            if (!$vmElidId) {
                throw new InvalidParamException('Ошибка создания VPS ' . $accountTariffId);
            }

            // ... и запомнить
            $accountTariff->vm_elid_id = $vmElidId;
            if (!$accountTariff->save()) {
                throw new ModelValidationException($accountTariff);
            }
        }
    }

    /**
     * Синхронизировать ресурсы в VPS manager
     *
     * @param int $clientAccountId
     * @param int $accountTariffId
     * @param int[] $accountTariffResourceIds
     * @link http://bugtracker.welltime.ru/jira/browse/BIL-3947
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function syncResource($clientAccountId, $accountTariffId, $accountTariffResourceIds)
    {
        $resources = [];

        if (!$accountTariffResourceIds) {
            return;
        }

        foreach ($accountTariffResourceIds as $accountTariffResourceId) {
            $accountTariffResourceLog = AccountTariffResourceLog::findOne(['id' => $accountTariffResourceId]);
            if (!$accountTariffResourceLog) {
                throw new \InvalidArgumentException('Wrong AccountTariffResourceLogId = ' . $accountTariffResourceId);
            }

            $resources[$accountTariffResourceLog->resource_id] = $accountTariffResourceLog->getAmount();
        }

        $accountTariff = AccountTariff::findOne(['id' => $accountTariffId]);

        ApiVps::me()->vpsUpdate(
            $accountTariff->vm_elid_id,
            isset($resources[Resource::ID_VPS_RAM]) ? $resources[Resource::ID_VPS_RAM] : null,
            isset($resources[Resource::ID_VPS_PROCESSOR]) ? $resources[Resource::ID_VPS_PROCESSOR] : null,
            isset($resources[Resource::ID_VPS_HDD]) ? $resources[Resource::ID_VPS_HDD] : null
        );

        if (isset($resources[Resource::ID_VPS_HDD])) {
            Trouble::dao()->createTrouble(
                $clientAccountId,
                Trouble::TYPE_TASK,
                Trouble::SUBTYPE_TASK,
                sprintf('Для VPS ELID ID = %d изменить ресурс HDD на %d GB. УУ %s', $accountTariff->vm_elid_id, $resources[Resource::ID_VPS_HDD], $accountTariff->getUrl()),
                null,
                Trouble::DEFAULT_VPS_SUPPORT
            );
        }
    }

    /**
     * Уведомить о покупке доп. услуг
     *
     * @param int $accountTariffId
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function syncLicense($accountTariffId)
    {
        $accountTariff = AccountTariff::findOne(['id' => $accountTariffId]);
        Trouble::dao()->createTrouble(
            $accountTariff->client_account_id,
            Trouble::TYPE_TASK,
            Trouble::SUBTYPE_TASK,
            'Доп. услуга VPS. УУ ' . $accountTariff->getUrl(),
            null,
            Trouble::DEFAULT_VPS_SUPPORT
        );
    }

    /**
     * Включить клиента в VPS, если он там есть
     *
     * @param int $clientAccountId
     * @return mixed
     * @throws \Exception
     */
    public function enableAccount($clientAccountId)
    {
        return $this->enableOrDisableAccount($clientAccountId, $isEnable = true);
    }

    /**
     * Включить клиента в VPS, если он там есть
     *
     * @param int $clientAccountId
     * @return mixed
     * @throws \Exception
     */
    public function disableAccount($clientAccountId)
    {
        return $this->enableOrDisableAccount($clientAccountId, $isEnable = false);
    }

    /**
     * Включить клиента в VPS, если он там есть
     *
     * @param int $clientAccountId
     * @param bool $isEnable
     * @return mixed|null|bool
     * @throws \Exception
     */
    protected function enableOrDisableAccount($clientAccountId, $isEnable)
    {
        $apiVps = ApiVps::me();
        if (!$apiVps->isAvailable()) {
            return null;
        }

        $clientAccount = ClientAccount::findOne(['id' => $clientAccountId]);
        $vmClientId = (int)$this->getVmUserInfo($clientAccount);
        if (!$vmClientId) {
            return false;
        }

        return $apiVps->userEnableOrDisable($vmClientId, $isEnable);
    }

    /**
     * Вернуть ID клиента в VPS
     *
     * @param ClientAccount $clientAccount
     * @return int
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     * @throws \app\exceptions\ModelValidationException
     */
    protected function getVmClientId(ClientAccount $clientAccount)
    {
        // взять из кэша
        $vmClientId = (int)$this->getVmUserInfo($clientAccount);
        if ($vmClientId) {
            return $vmClientId;
        }

        $apiVps = ApiVps::me();
        $vmClientId = $apiVps->userCreate($name = 'client_' . $clientAccount->id, $password = Utils::password_gen());
        if (!$vmClientId) {
            throw new \LogicException('Ошибка создания клиента в Vps');
        }

        // сохранить в кэш
        // id
        $clientAccountOptions = new ClientAccountOptions;
        $clientAccountOptions->client_account_id = $clientAccount->id;
        $clientAccountOptions->option = self::CLIENT_ACCOUNT_OPTION_VPS_ELID;
        $clientAccountOptions->value = (string)$vmClientId;
        if (!$clientAccountOptions->save()) {
            throw new ModelValidationException($clientAccountOptions);
        }

        unset($clientAccountOptions);

        // пароль
        $clientAccountOptions = new ClientAccountOptions;
        $clientAccountOptions->client_account_id = $clientAccount->id;
        $clientAccountOptions->option = self::CLIENT_ACCOUNT_OPTION_VPS_PASSWORD;
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
    public function getVmUserInfo(ClientAccount $clientAccount, $option = self::CLIENT_ACCOUNT_OPTION_VPS_ELID)
    {
        $options = $clientAccount->getOption($option);
        if (count($options)) {
            return $options[0];
        }

        return null;
    }
}
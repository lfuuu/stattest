<?php

namespace app\dao;

use app\classes\Assert;
use app\classes\Singleton;
use app\models\Business;
use app\models\ClientAccount;
use app\models\UsageTrunk;
use app\models\UsageVoip;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;

/**
 * Class UsageDao
 *
 * @method static UsageDao me($args = null)
 */
class UsageDao extends Singleton
{
    protected $usageClass = null;

    /** @var ClientAccount */
    private $_account = null;

    private $_mapUsageClassToServiceTypeId = [];

    public $lastErrorMessage = "";

    /**
     * Инициализация
     */
    public function init()
    {
        $this->_mapUsageClassToServiceTypeId = [
            UsageTrunk::className() => ServiceType::ID_TRUNK,
            UsageVoip::className() => ServiceType::ID_VOIP
        ];
    }

    /**
     * Можно ли добавить услугу
     *
     * @param ClientAccount $account
     * @param string|null $serviceTypeId передается только если услуга универсальная
     * @return bool
     */
    public function isPossibleAddService(ClientAccount $account, $serviceTypeId = null)
    {
        Assert::isObject($account);

        if ($account->account_version == ClientAccount::VERSION_BILLER_UNIVERSAL && !$serviceTypeId) {
            throw new \BadMethodCallException('Не указан тип услуги');
        }

        if (!in_array($account->account_version, [
            ClientAccount::VERSION_BILLER_UNIVERSAL,
            ClientAccount::VERSION_BILLER_USAGE
        ])
        ) {
            throw new \LogicException('Неправильный тип ЛС');
        }

        if ($account->account_version == ClientAccount::VERSION_BILLER_USAGE) {
            $serviceTypeId = $this->_mapUsageClassToServiceType($this->usageClass);
        }

        $this->_account = $account;

        return $this->_isPossibleAddService($serviceTypeId);
    }

    /**
     * Проверка возможности добавления услуги
     *
     * @param string $serviceTypeId
     * @return bool
     */
    private function _isPossibleAddService($serviceTypeId)
    {
        if (
            $this->_account->account_version == ClientAccount::VERSION_BILLER_UNIVERSAL
            && $serviceTypeId == ServiceType::ID_TRUNK
        ) {
            return $this->_returnValue(
                !$this->_hasService(ServiceType::ID_TRUNK),
                'Для ЛС можно создать только одну базовую услугу транка. Зато можно добавить несколько пакетов.'
            );
        }

        switch ($this->_account->contract->business_id) {
            case Business::OPERATOR: {
                return $this->_returnValue(
                    in_array($serviceTypeId, [ServiceType::ID_TRUNK, ServiceType::ID_INFRASTRUCTURE]),
                    'Для ЛС с подразделением Межоператорка можно добавить только транки или инфраструктуру'
                );
            }

            default: {
                // или транк, или телефония. Что-то одно
                switch ($serviceTypeId) {
                    case ServiceType::ID_TRUNK: {
                        return $this->_returnValue(
                            !$this->_hasService(ServiceType::ID_VOIP),
                            'Транк не совместим с данным ЛС'
                        );
                    }

                    case ServiceType::ID_VOIP: {
                        return $this->_returnValue(
                            !$this->_hasService(ServiceType::ID_TRUNK),
                            'Услуга номера не совместима с данным ЛС'
                        );
                    }

                    default: {
                        return true;
                    }
                }
            }
        }
    }

    /**
     * Транслируем тип "неуниверсальной" услуги в универсальную
     *
     * @param string $usageClass
     * @return string
     */
    private function _mapUsageClassToServiceType($usageClass)
    {
        if (!isset($this->_mapUsageClassToServiceTypeId[$usageClass])) {
            throw new \LogicException('Неизвестный тип услуги');
        }

        return $this->_mapUsageClassToServiceTypeId[$usageClass];
    }

    /**
     * @param integer $serviceTypeId
     * @return bool
     */
    private function _hasService($serviceTypeId)
    {
        if ($this->_account->account_version == ClientAccount::VERSION_BILLER_USAGE) {
            $usageClass = array_search($serviceTypeId, $this->_mapUsageClassToServiceTypeId);

            if (!$usageClass) {
                throw new \LogicException('Неизвестный тип услуги');
            }

            return $usageClass::dao()->hasService($this->_account);
        }

        return $this->_hasUniversalService($serviceTypeId);
    }

    /**
     * Если ли униварсальная услуга на ЛС
     *
     * @param integer $serviceTypeId
     * @return bool
     */
    private function _hasUniversalService($serviceTypeId)
    {
        return (bool)AccountTariff::find()
            ->where([
                'client_account_id' => $this->_account->id,
                'service_type_id' => $serviceTypeId,
            ])->count();
    }

    /**
     * Устанавливаем текст ошибки, если результат проверки отрицательный
     *
     * @param bool $value
     * @param string $errorStr
     * @return bool
     */
    private function _returnValue($value, $errorStr = "")
    {
        $this->lastErrorMessage = "";

        if (!$value) {
            $this->lastErrorMessage = $errorStr;
        }

        return $value;
    }
}
<?php

namespace app\modules\uu\models\traits;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Business;
use app\models\ClientAccount;
use app\models\Number;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use DateTime;
use Yii;

trait AccountTariffValidatorTrait
{
    /** @var int Код ошибки для АПИ */
    public $errorCode = null;

    /** @var int */
    private $_serviceTypeIdOld = null;

    /** @var int */
    public $tariffPeriodIdOld = null;

    /**
     * This method is called when the AR object is created and populated with the query result.
     * The default implementation will trigger an [[EVENT_AFTER_FIND]] event.
     * When overriding this method, make sure you call the parent implementation to ensure the
     * event is triggered.
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->_serviceTypeIdOld = $this->service_type_id;
        $this->tariffPeriodIdOld = $this->tariff_period_id;
    }

    /**
     * Валидировать, что нельзя менять service_type_id
     *
     * @param string $attribute
     * @param array $params
     */
    public function validatorServiceType($attribute, $params)
    {
        if (!$this->isNewRecord && $this->_serviceTypeIdOld && $this->_serviceTypeIdOld != $this->service_type_id) {
            $this->addError($attribute, 'Нельзя менять тип услуги');
            $this->errorCode = AccountTariff::ERROR_CODE_SERVICE_TYPE;
            return;
        }
    }

    /**
     * Валидировать, что задан транк для соответствующей услуги
     *
     * @param string $attribute
     * @param array $params
     */
    public function validatorTrunk($attribute, $params)
    {
        if ($this->service_type_id != ServiceType::ID_TRUNK) {
            return;
        }

        if (
            $this->isNewRecord
            && AccountTariff::find()
                ->where(
                    [
                        'client_account_id' => $this->client_account_id,
                        'service_type_id' => $this->service_type_id,
                    ]
                )
                ->count()
        ) {
            $this->addError($attribute, 'Для ЛС можно создать только одну базовую услугу транка. Зато можно добавить несколько пакетов.');
            $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_TRUNK_SINGLE;
            return;
        }

        if (!in_array($this->clientAccount->contract->business_id, [Business::OPERATOR, Business::OTT])) {
            $this->addError($attribute, 'Универсальную услугу транка можно добавить только ЛС с договором Межоператорка или ОТТ.');
            $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_TRUNK;
            return;
        }
    }

    /**
     * Валидировать, что tariff_period_id соответствует service_type_id
     *
     * @param string $attribute
     * @param array $params
     */
    public function validatorTariffPeriod($attribute, $params)
    {
        if (!$this->tariff_period_id) {
            return;
        }

        $tariffPeriod = $this->tariffPeriod;
        if (!$tariffPeriod) {
            $this->addError($attribute, 'Неправильный тариф/период ' . $this->tariff_period_id);
            $this->errorCode = AccountTariff::ERROR_CODE_TARIFF_WRONG;
            return;
        }

        if ($tariffPeriod->tariff->service_type_id != $this->service_type_id) {
            $this->addError($attribute, 'Тариф/период ' . $tariffPeriod->tariff->service_type_id . ' не соответствует типу услуги ' . $this->service_type_id);
            $this->errorCode = AccountTariff::ERROR_CODE_TARIFF_SERVICE_TYPE;
            return;
        }
    }

    /**
     * Валидировать, что номер свободный
     *
     * @param string $attribute
     * @param array $params
     * @throws \app\exceptions\ModelValidationException
     */
    public function validatorVoipNumber($attribute, $params)
    {
        /** @var \app\models\Number $number */
        if (!$this->voip_number || !($number = $this->number)) {
            return;
        }

        if ($this->isNewRecord && $number->status != Number::STATUS_INSTOCK) {
            $this->addError($attribute, 'Этот телефонный номер нельзя подключить');
            $this->errorCode = AccountTariff::ERROR_CODE_USAGE_NUMBER_NOT_IN_STOCK;
            return;
        }
    }

    /**
     * УУ-ЛС?
     *
     * @param int $clientAccountId
     * @return bool|null null - нет клиента, false - 4 (старый), true - 5 (УУ)
     */
    public static function isUuAccount($clientAccountId = null)
    {
        if (!$clientAccountId) {
            global $fixclient_data;
            if (isset($fixclient_data['id']) && $fixclient_data['id'] > 0) {
                $clientAccountId = (int)$fixclient_data['id'];

            }
        }

        if (!$clientAccountId) {
            return null;
        }

        $clientAccount = ClientAccount::findOne(['id' => $clientAccountId]);
        if ($clientAccount->account_version == ClientAccount::VERSION_BILLER_UNIVERSAL) {
            Yii::$app->session->setFlash('error', 'Неуниверсальную услугу можно добавить только ЛС, тарифицируемому неуниверсально.');
            return true;
        }

        return false;
    }

    /**
     * Дата по умолчанию для подключить/сменить/закрыть
     *
     * Подключить и закрыть - строго после этой даты.
     * Сменить тариф - с любой даты, но эта по умолчанию.
     *
     * @return string
     */
    public function getDefaultActualFrom()
    {
        $date = date(DateTimeZoneHelper::DATE_FORMAT);
        if ($this->isNewRecord) {
            // подключение с сегодня
            return $date;
        }

        /** @var AccountLogPeriod[] $accountLogPeriods */
        $accountLogPeriods = $this->accountLogPeriods;
        if (count($accountLogPeriods)) {
            // после завершения оплаченного
            $accountLogPeriod = end($accountLogPeriods);
            $date = max($accountLogPeriod->date_to, $date);
        }

        // смена/закрытие с завтра или следующего периода
        return (new DateTime($date))
            ->modify('+1 day')
            ->format(DateTimeZoneHelper::DATE_FORMAT);
    }
}
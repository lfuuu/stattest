<?php

namespace app\modules\uu\models\traits;

use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\Number;
use app\modules\nnp\models\NdcType;
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
            // не транк
            return;
        }

        // мега/мульти транк
        if (!$this->isNewRecord) {
            return;
        }

        $condition = [];
        // BIL-4612
        // Для добавления транка не должно быть мегатранков.
        // Для добавления мегатранка, не должно быть транков.
        if ($this->trunk_type_id != AccountTariff::TRUNK_TYPE_MEGATRUNK) {
            $condition['trunk_type_id'] = AccountTariff::TRUNK_TYPE_MEGATRUNK;
        }

        if (AccountTariff::find()
            ->where([
                'client_account_id' => $this->client_account_id,
                'service_type_id' => $this->service_type_id,
            ])
            ->andWhere($condition)
            ->count()
        ) {
            $this->addError($attribute, 'Для ЛС можно создать только один мегатранк или транки других типов.');
            $this->errorCode = AccountTariff::ERROR_CODE_ACCOUNT_TRUNK_SINGLE;
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
            $this->addError($attribute, 'Неправильный тариф / период ' . $this->tariff_period_id);
            $this->errorCode = AccountTariff::ERROR_CODE_TARIFF_WRONG;
            return;
        }

        if ($tariffPeriod->tariff->service_type_id != $this->service_type_id) {
            $this->addError($attribute, 'Тариф / период ' . $tariffPeriod->tariff->service_type_id . ' не соответствует типу услуги ' . $this->service_type_id);
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
        if ($this->service_type_id != ServiceType::ID_VOIP) {
            return;
        }

        $number = null;
        if (
            $this->isNewRecord && (
                !$this->voip_number
                || !($number = $this->number)
                || $number->status != Number::STATUS_INSTOCK
            )) {
            $this->addError($attribute, 'Этот телефонный номер нельзя подключить');
            $this->errorCode = AccountTariff::ERROR_CODE_USAGE_NUMBER_NOT_IN_STOCK;
            return;
        }

        if ($number) {
            if ($number->ndc_type_id == NdcType::ID_FREEPHONE && !$this->region_id) {
                $this->region_id = $number->region ?: $number->country->default_connection_point_id;
            }

            $this->city_id = $number->city_id;
        }
    }

    public function validateRegion($attribute, $params)
    {
        // только новые подключения
        if (!$this->isNewRecord) {
            return;
        }

        if ($this->service_type_id == ServiceType::ID_SIPTRUNK && $this->region && !$this->region->is_use_sip_trunk) {
            $this->addError($attribute, 'Допустимы только регионы, которые используются в SIP-транках');
        }

        if ($this->service_type_id == ServiceType::ID_VPBX && $this->region && !$this->region->is_use_vpbx) {
            $this->addError($attribute, 'Регион не используется для подключения ВАТС');
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
     * @throws \Exception
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
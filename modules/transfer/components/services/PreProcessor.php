<?php

namespace app\modules\transfer\components\services;

use app\classes\Assert;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use DateTime;
use DateTimeZone;
use kartik\base\Config;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\base\InvalidValueException;
use yii\base\Model;

/**
 * @property-read ServiceTransfer $serviceHandler
 * @property-read ServiceTransfer $targetServiceHandler
 * @property-read ClientAccount $clientAccount
 * @property-read ClientAccount $targetClientAccount
 */
class PreProcessor extends Model
{

    /** @var Processor */
    public $processor;

    /** @var ServiceTransfer */
    public $sourceServiceHandler;

    /** @var ServiceTransfer */
    public $targetServiceHandler;

    /** @var ClientAccount */
    public $clientAccount;

    /** @var ClientAccount */
    public $targetClientAccount;

    /** @var string */
    public $activationDate;

    /** @var string */
    public $activationDatetime;

    /** @var string */
    public $expireDate;

    /** @var string */
    public $expireDatetime;

    /** @var int */
    public $tariffId;

    /** @var string */
    public $serviceType;

    /** @var \stdClass|null */
    public $relation;

    /** @var string */
    private $_processFromDate;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'processor',
                    'clientAccount',
                    'targetClientAccount',
                    'sourceServiceHandler',
                    'targetServiceHandler',
                    'activationDate',
                    'activationDatetime',
                    'expireDate',
                    'expireDatetime',
                ],
                'required'
            ],
            ['tariffId', 'validateTariffRequired',],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'clientAccount' => 'Исходный лицевой счет',
            'targetClientAccount' => 'Целевой лицевой счет',
            'sourceServiceHandler' => 'Тип обработчика исходной услуги',
            'targetServiceHandler' => 'Тип обработчика целевой услуги',
            'activationDate' => 'Дата активации услуги',
            'activationDatetime' => 'Дата/Время активации услуги',
            'expireDate' => 'Дата окончания услуги',
            'expireDatetime' => 'Дата/Время окончания услуги',
        ];
    }

    /**
     * @inheritdoc
     */
    public function validateTariffRequired()
    {
        if ($this->targetClientAccount->account_version > $this->clientAccount->account_version && !(int)$this->tariffId) {
            $this->addError('tariffId', 'Необходимо указать тарифный план');
        }
    }

    /**
     * @param Processor $processor
     * @return $this
     */
    public function setProcessor(Processor $processor)
    {
        $this->processor = $processor;

        return $this;
    }

    /**
     * @param $serviceType
     * @return $this
     */
    public function setServiceType($serviceType)
    {
        $this->serviceType = $serviceType;

        return $this;
    }

    /**
     * @param ServiceTransfer $serviceHandler
     * @param int $serviceId
     * @return $this
     * @throws InvalidCallException
     */
    public function setService(ServiceTransfer $serviceHandler, $serviceId)
    {
        $this->sourceServiceHandler = $serviceHandler->setServiceById($serviceId);

        return $this;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function setProcessedFromDate($date)
    {
        $this->_processFromDate = new DateTime($date);

        $this->activationDate = $this->_processFromDate->format(DateTimeZoneHelper::DATE_FORMAT);

        /** @var DateTime $expireDate */
        $expireDate = clone $this->_processFromDate;
        $this->expireDate = $expireDate
            ->modify('-1 day')
            ->format(DateTimeZoneHelper::DATE_FORMAT);

        /** @var DateTime $activationDatetime */
        $activationDatetime = clone $this->_processFromDate;
        $this->activationDatetime = $activationDatetime
            ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT))
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        $expireDatetime = clone $this->_processFromDate;
        $this->expireDatetime = $expireDatetime
            ->modify('-1 day')
            ->setTime(23, 59, 59)
            ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC))
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        return $this;
    }

    /**
     * @param int $clientAccountId
     * @return $this
     */
    public function setSourceClientAccount($clientAccountId)
    {
        $this->clientAccount = ClientAccount::findOne(['id' => $clientAccountId]);
        Assert::isObject($this->clientAccount, 'Missing ClientAccount #' . $clientAccountId);

        return $this;
    }

    /**
     * @param int $clientAccountId
     * @return $this
     * @throws InvalidCallException
     * @throws InvalidParamException
     * @throws InvalidValueException
     */
    public function setTargetClientAccount($clientAccountId)
    {
        $this->targetClientAccount = ClientAccount::findOne(['id' => $clientAccountId]);
        Assert::isObject($this->targetClientAccount, 'Missing ClientAccount #' . $clientAccountId);

        /** @var \app\modules\transfer\Module $module */
        $module = Config::getModule('transfer');
        $this->targetServiceHandler = $module
            ->getServiceProcessor($this->targetClientAccount->account_version)
            ->getHandler($this->serviceType);

        return $this;
    }

    /**
     * @param int $tariffId
     * @return $this
     */
    public function setTariff($tariffId = 0)
    {
        $this->tariffId = $tariffId;

        return $this;
    }

    /**
     * Set description of relation with parent service
     *
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    public function setRelation($field, $value)
    {
        $this->relation = new \stdClass;
        $this->relation->field = $field;
        $this->relation->value = $value;

        return $this;
    }

}
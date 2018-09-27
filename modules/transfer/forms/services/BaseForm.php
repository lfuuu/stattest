<?php

namespace app\modules\transfer\forms\services;

use app\classes\Form;
use app\classes\validators\ArrayValidator;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\modules\transfer\components\services\PreProcessor;
use app\modules\transfer\components\services\Processor;
use DateTime;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\base\InvalidValueException;

/**
 * @property-read string $nearestPossibleDate
 * @property-read array $servicesPossibleToTransfer
 */
abstract class BaseForm extends Form
{

    const NEAREST_POSSIBLE_DATE = 'next day midnight';

    const NEAREST_MONTH_DATE = 'first day of next month midnight';

    /** @var int */
    public $clientAccountId;

    /** @var int */
    public $targetClientAccountId;

    /** @var string */
    public $processedFromDate;

    /**
     * @var array - ServiceTypeKey => ServiceIds[]
     */
    public $services;

    /** @var array */
    public $tariffIds = [];

    /** @var array */
    public $fromDate = [];

    /** @var ClientAccount */
    public $clientAccount;

    /**
     * Used only for form integrity
     *
     * @var string
     */
    public $targetClientAccount;

    /** @var Processor */
    public $processor;

    /**
     * @var array
     */
    public $processLog = [];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['clientAccountId', 'targetClientAccountId',], 'integer'],
            ['processedFromDate', 'string'],
            [['services', 'tariffIds', 'fromDate'], ArrayValidator::class],
            [
                [
                    'clientAccountId', 'targetClientAccountId', 'processedFromDate', 'services',
                    'processor',
                ], 'required'
            ],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'clientAccountId' => 'Исходный лицевой счет',
            'targetClientAccountId' => 'Целевой лицевой счет',
            'processedFromDate' => 'Дата переноса',
            'services' => 'Список услуг',
        ];
    }

    /**
     * @return string
     */
    public function getNearestPossibleDate()
    {
        return (new DateTime(self::NEAREST_POSSIBLE_DATE))
            ->format(DateTimeZoneHelper::DATE_FORMAT);
    }

    /**
     * @return string
     */
    public function getNearestMonthDate()
    {
        return (new DateTime(self::NEAREST_MONTH_DATE))
            ->format(DateTimeZoneHelper::DATE_FORMAT);
    }

    /**
     * @throws ModelValidationException
     * @throws InvalidParamException
     * @throws InvalidValueException
     * @throws InvalidCallException
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function process()
    {
        try {
            if (!(
                $this->load(\Yii::$app->request->post(), 'data')
                && $this->validate()
            )) {
                throw new ModelValidationException($this);
            }
        } catch (ModelValidationException $e) {
            return false;
        }

        foreach ($this->services as $serviceType => $serviceIds) {
            foreach ($serviceIds as $index => $serviceId) {
                $preProcessor = null;

                try {
                    $preProcessor = (new PreProcessor)
                        ->setProcessor($this->processor)
                        ->setServiceType($serviceType)
                        ->setService($this->processor->getHandler($serviceType), $serviceId)
                        ->setProcessedFromDate(isset($this->fromDate[$serviceType], $this->fromDate[$serviceType][$index]) ? $this->fromDate[$serviceType][$index] : $this->processedFromDate)
                        ->setSourceClientAccount($this->clientAccountId)
                        ->setTargetClientAccount($this->targetClientAccountId)
                        ->setTariff(isset($this->tariffIds[$serviceType], $this->tariffIds[$serviceType][$index]) ? $this->tariffIds[$serviceType][$index] : 0);

                    $this->processLog[] = [
                        'type' => 'success',
                        'object' => $this->processor->run($preProcessor),
                    ];
                } catch (ModelValidationException $e) {
                    $model = $e->getModel();

                    $this->processLog[] = [
                        'type' => 'error',
                        'message' => get_class($model) . ' (' . $e->getLine() . '): ' . $e->getMessage(),
                        'object' => $preProcessor,
                    ];
                } catch (\Exception $e) {
                    $this->processLog[] = [
                        'type' => 'error',
                        'message' => $e->getMessage(),
                        'object' => $preProcessor,
                    ];
                }
            }
        }

        return true;
    }

    /**
     * @return array
     */
    abstract public function getServicesPossibleToTransfer();

}

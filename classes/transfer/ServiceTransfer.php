<?php

namespace app\classes\transfer;

use Yii;
use app\classes\Assert;
use app\models\ClientAccount;
use app\models\Usage;
use \DateTime;
use \DateTimeZone;
use yii\base\InvalidValueException;
/**
 * Абстрактный класс переноса услуг
 * @package app\classes\transfer
 */
abstract class ServiceTransfer
{
    protected $targetAccount;
    protected $activationDate;

    /** @var Usage */
    public $service;

    /**
     * Конструктор класса
     * @param Usage $service - экземпляр услуги
     */
    public function __construct(Usage $service)
    {
        $this->service = $service;
    }

    /**
     * Устанавливает лицевой счет на который предполагается перенос
     * @param ClientAccount $targetAccount - лицевой счет на который осуществляется перенос услуги
     * @return $this
     */
    public function setTargetAccount(ClientAccount $targetAccount)
    {
        $this->targetAccount = $targetAccount;
        return $this;
    }

    /**
     * Устанавливает дату активации переносимых услуг
     * @param $date - дата активации
     * @return $this
     */
    public function setActivationDate($date)
    {
        $this->activationDate = new DateTime($date, $this->targetAccount->timezone);
        return $this;
    }

    /**
     * Перенос базовой сущности услуги
     * @return object - созданная услуга
     */
    public function process()
    {
        if ($this->service->actual_to < $this->getActualDate())
            throw new InvalidValueException('Услуга не может быть перенеса на указанную дату');

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            $targetService = new $this->service;
            $targetService->setAttributes($this->service->getAttributes(), false);
            unset($targetService->id);
            $targetService->activation_dt = $this->getActivationDatetime();
            $targetService->actual_from = $this->getActualDate();
            $targetService->prev_usage_id = $this->service->id;
            $targetService->client = $this->targetAccount->client;

            $targetService->save();

            $this->service->expire_dt = $this->getExpireDatetime();
            $this->service->actual_to = $this->getExpireDate();
            $this->service->next_usage_id = $targetService->id;

            $this->service->save();

            $dbTransaction->commit();
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }

        return $targetService;
    }

    /**
     * Процесс отмены переноса услуги, в простейшем варианте, только манипуляции с записями
     */
    public function fallback()
    {
        if (!(int)$this->service->next_usage_id)
            throw new InvalidValueException('Услуга не была подготовлена к переносу');

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            $movedService = new $this->service;
            $movedService = $movedService->find()
                ->andWhere(['id' => $this->service->next_usage_id])
                ->andWhere('actual_from > :date', [':date' => (new \DateTime())->format('Y-m-d')])
                ->one();
            Assert::isObject($movedService);

            $this->service->next_usage_id = 0;
            $this->service->expire_dt = $movedService->expire_dt;
            $this->service->actual_to = $movedService->actual_to;

            $this->service->save();

            $movedService->delete();
            $dbTransaction->commit();
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }
    }

    public function getActivationDatetime()
    {
        $activationDatetime = clone $this->activationDate;
        return $activationDatetime
                    ->setTimezone(new DateTimeZone('UTC'))
                    ->format('Y-m-d H:i:s');
    }

    public function getActualDate()
    {
        return $this->activationDate->format('Y-m-d');
    }

    public function getExpireDatetime()
    {
        $expireDatetime = clone $this->activationDate;
        return $expireDatetime
                    ->modify('-1 day')
                    ->setTime(23, 59, 59)
                    ->setTimezone(new DateTimeZone('UTC'))
                    ->format('Y-m-d H:i:s');
    }

    public function getExpireDate()
    {
        $expireDate = clone $this->activationDate;
        return $expireDate
                    ->modify('-1 day')
                    ->format('Y-m-d');
    }

}
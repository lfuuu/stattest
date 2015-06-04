<?php

namespace app\classes\transfer;

use Yii;
use app\classes\Assert;
use app\models\ClientAccount;
use app\models\Usage;
use \DateTime;
use \DateTimeZone;
/**
 * Абстрактный класс переноса услуг
 * @package app\classes\transfer
 */
abstract class ServiceTransfer
{
    // Дата активации услуги при переносе, по-умолчанию
    protected $activation_date;

    /** @var Usage */
    protected $service;

    /**
     * Конструктор класса
     * @param Usage $service - экземпляр услуги
     */
    public function __construct(Usage $service)
    {
        $this->service = $service;
    }

    /**
     * Перенос базовой сущности услуги
     * @param ClientAccount $targetAccount - лицевой счет на который осуществляется перенос услуги
     * @return object - созданная услуга
     */
    public function process(ClientAccount $targetAccount, $activationDate)
    {
        //throw new \Exception('Услуга не готова к переносу');

        if ((int) $this->service->next_usage_id)
            throw new \Exception('Услуга уже перенесена');

        $dstActualFrom = new DateTime($activationDate, $targetAccount->timezone);
        $dstActivationDt = clone $dstActualFrom;
        $dstActivationDt->setTimezone(new DateTimeZone('UTC'));

        $srcActualTo = clone $dstActualFrom;
        $srcActualTo->modify('-1 day');
        $srcExpireDt = clone $srcActualTo;
        $srcExpireDt->setTime(23, 59, 59);
        $srcExpireDt->setTimezone(new DateTimeZone('UTC'));


        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            $targetService = new $this->service;
            $targetService->setAttributes($this->service->getAttributes(), false);
            unset($targetService->id);
            $targetService->activation_dt = $dstActivationDt->format('Y-m-d H:i:s');
            $targetService->actual_from = $dstActualFrom->format('Y-m-d');
            $targetService->prev_usage_id = $this->service->id;
            $targetService->client = $targetAccount->client;

            $targetService->save();

            $this->service->expire_dt = $srcExpireDt->format('Y-m-d H:i:s');
            $this->service->actual_to = $srcActualTo->format('Y-m-d');
            $this->service->next_usage_id = $targetService->id;

            $this->service->save();

            $dbTransaction->commit();
        }
        catch (\Exception $e) {
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
        if (!(int) $this->service->next_usage_id)
            throw new \Exception('Услуга не была подготовлена к переносу');

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
        }
        catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }
    }
}
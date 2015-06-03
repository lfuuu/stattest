<?php

namespace app\classes\transfer;

use Yii;
use app\models\ClientAccount;
use app\models\Usage;

/**
 * Абстрактный класс переноса услуг
 * @package app\classes\transfer
 */
abstract class ServiceTransfer
{
    // Дата активации услуги при переносе, по-умолчанию
    protected $activation_date = 'first day of next month midnight';

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
     * Установка даты активации услуги при переносе
     * @param $date - предпологаемая дата, timestamp|string|strtotime format
     * @return $this
     */
    public function setActivationDate($date)
    {
        if (is_numeric($date))
            $this->activation_date = $date;
        else if (preg_match('#([0-9]{2})\.([0-9]{2})\.([0-9]{4})#', $date, $match))
            $this->activation_date = mktime(0, 0, 0, $match[2], $match[1], $match[3]);
        else if(preg_match('#([0-9]{4})\-([0-9]{2})\-([0-9]{2})#', $date, $match))
            $this->activation_date = mktime(0, 0, 0, $match[2], $match[3], $match[1]);
        else if(is_string($date) && !empty($date))
            $this->activation_date = strtotime($date);
        else
            $this->activation_date = strtotime($this->activation_date);

        return $this;
    }

    /**
     * Получение значения даты активации услуги
     * @return string
     */
    public function getActivationDate()
    {
        return $this->activation_date;
    }

    /**
     * Перенос базовой сущности услуги
     * @param ClientAccount $targetAccount - лицевой счет на который осуществляется перенос услуги
     * @return object - созданная услуга
     */
    public function process(ClientAccount $targetAccount)
    {
        //throw new \Exception('Услуга не готова к переносу');

        if ((int) $this->service->dst_usage_id)
            throw new \Exception('Услуга уже перенесена');

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            $targetService = new $this->service;
            $targetService->setAttributes($this->service->getAttributes(), false);
            unset($targetService->id);
            $targetService->activation_dt = date('Y-m-d H:i:s', $this->activation_date);
            $targetService->actual_from = date('Y-m-d', $this->activation_date);
            $targetService->src_usage_id = $this->service->id;
            $targetService->client = $targetAccount->client;

            $targetService->save();

            $this->service->expire_dt = date('Y-m-d H:i:s', $this->activation_date - 1);
            $this->service->actual_to = date('Y-m-d', $this->activation_date - 1);
            $this->service->dst_usage_id = $targetService->id;

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

        $now = new \DateTime();
        $movedService = new $this->service;
        $movedService = $movedService->find()
            ->andWhere(['id' => $this->service->dst_usage_id])
            ->andWhere('actual_from > :date', [':date' => $now->format('Y-m-d')])
            ->one();
        print_r($movedService);
        die();
        if (!(int) $this->service->dst_usage_id)
            throw new \Exception('Услуга не была подготовлена к переносу');

        $now = new \DateTime();

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            $movedService = new $this->service;
            $movedService = $movedService->find()
                ->andWhere(['id' => $this->service->dst_usage_id])
                ->andWhere('actual_from > :date', [':date' => $now->format('Y-m-d')])
                ->one();
            Assert::isObject($movedService);

            $this->service->dst_usage_id = 0;
            $this->service->expire_dt = $movedService->expire_dt;
            $this->service->actual_to = $movedService->actual_to;

            $this->service->source->save();

            $movedService->delete();
            $dbTransaction->commit();
        }
        catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }
    }

}
<?php

namespace app\classes\transfer;

use Yii;
use app\classes\Assert;
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
        if ((int) $date)
            $this->activation_date = $date;
        else if (preg_match('#([0-9]{2})\.([0-9]{2})\.([0-9]{4})#', $date, $match))
            $this->activation_date = mktime(0, 0, 0, $match[2], $match[1], $match[3]);
        else if(preg_match('#([0-9]{4})\-([0-9]{2})\-([0-9]{2})#', $date, $match))
            $this->activation_date = mktime(0, 0, 0, $match[2], $match[3], $match[1]);
        else if(!empty($date))
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
        try
        {
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
        catch (\Exception $e)
        {
            $dbTransaction->rollBack();
            throw $e;
        }

        return $targetService;
    }

    /**
     * Процесс отмены переноса услуги, в простейшем варианте, только манипуляции с записями
     * @param $record - объект модели услуги для которой надо сделать отмену
     * @throws Exception
     */
    public function cancel($source)
    {
        /*
        if (!(int) $source->dst_usage_id)
            throw new \Exception('Услуга не была подготовлена к переносу');

        $clone = new $source;
        $transfer = $clone->findOne($this->dst_usage_id);
        Assert::isObject($transfer);

        $source->dst_usage_id = 0;
        $source->expire_dt = $transfer->expire_dt;
        $source->actual_to = $transfer->actual_to;

        $source->save();

        $transfer->delete();
        */
    }

}
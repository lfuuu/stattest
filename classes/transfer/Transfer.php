<?php

namespace app\classes\transfer;

use app\classes\Assert;
use yii\base\Exception;

/**
 * Абстрактный класс переноса услуг
 * @package app\classes\transfer
 */
abstract class Transfer
{

    /**
     * Процесс переноса услуги, в простейшей варианте, только манипуляции с записями
     * @param $source - объект модели услуги для которой надо сделать перенос
     * @throws Exception
     */
    public function process($source, $destination, $activation = '')
    {
        print '<pre>';

        if ((int) $source->dst_usage_id)
            throw new Exception('Услуга уже подготовлена к переносу');

        // Если дата активации переноса не указана, назначить на полночь первого дня след. месяца
        if (empty($activation))
            $activation_date = strtotime('first day of next month midnight');
        else
        {
            if (preg_match('#([0-9]{2})\.([0-9]{2})\.([0-9]{4})#', $activation, $match))
                $activation_date = mktime(0, 0, 0, $match[2], $match[1], $match[3]);
            else if(preg_match('#([0-9]{4})\-([0-9]{2})\-([0-9]{2})#', $activation, $match))
                $activation_date = mktime(0, 0, 0, $match[2], $match[3], $match[1]);
            else
                $activation_date = strtotime($activation);
        }

        $transfer = new $source;
        $transfer->setAttributes($source->getAttributes(), false);
        unset($transfer->id);
        $transfer->activation_dt = date('Y-m-d H:i:s', $activation_date);
        $transfer->actual_from = date('Y-m-d', $activation_date);
        $transfer->src_usage_id = $source->id;
        $transfer->client = $destination->id;

        print_r($transfer);
        //$transfer->save();

        $source->expire_dt = date('Y-m-d H:i:s', $activation_date - 1);
        $source->actual_to = date('Y-m-d', $activation_date - 1);
        $source->dst_usage_id = $transfer->id;

        //$source->save();
        print_r($source);
        print '</pre>';
    }

    /**
     * Процесс отмены переноса услуги, в простейшем варианте, только манипуляции с записями
     * @param $record - объект модели услуги для которой надо сделать отмену
     * @throws Exception
     */
    public function cancel($source)
    {
        if (!(int) $source->dst_usage_id)
            throw new Exception('Услуга не была подготовлена к переносу');

        $clone = new $source;
        $transfer = $clone->findOne($this->dst_usage_id);
        Assert::isObject($transfer);

        $source->dst_usage_id = 0;
        $source->expire_dt = $transfer->expire_dt;
        $source->actual_to = $transfer->actual_to;

        $source->save();

        $transfer->delete();
    }

}